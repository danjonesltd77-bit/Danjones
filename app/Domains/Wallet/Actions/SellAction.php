<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Core\Services\SettingService;
use App\Domains\Wallet\Contracts\GaspumpServiceInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\SystemWalletType;
use App\Enum\WalletStatus;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class SellAction
{
    public function __construct(
        private MarketDataGatewayInterface $marketDataGateway,
        private GaspumpServiceInterface $gaspumpService,
        private LedgerService $ledgerService,
        private SettingService $settingService
    ) {}

    /**
     * Sell cryptocurrency for NGN.
     */
    public function execute(User $user, Wallet $cryptoWallet, float $amount): array
    {
        if ($cryptoWallet->user_id !== $user->id) {
            throw new Exception('Unauthorized access to wallet.', 403);
        }

        if ($cryptoWallet->balance < $amount) {
            throw new Exception("Insufficient balance in {$cryptoWallet->currency->symbol} wallet.", 400);
        }

        $nairaWallet = $user->nairaWallet;
        if (! $nairaWallet) {
            throw new Exception('Naira wallet not found for user.', 400);
        }

        $cryptoUsdRate = $this->marketDataGateway->getExchangeRate($cryptoWallet->currency_id);
        $usdNgnRate = $this->marketDataGateway->getUsdNgnRate();

        // Calculate Fee (percentage based from settings)
        $feePercentage = $this->settingService->get('sell_fee_percentage', 1.5); // Default 1.5%
        $feeInCrypto = $amount * ($feePercentage / 100);

        if ($amount <= $feeInCrypto) {
            throw new Exception("Amount must be greater than the calculated fee of {$feeInCrypto} {$cryptoWallet->currency->symbol}.", 400);
        }

        $netCryptoAmount = $amount - $feeInCrypto;
        $netUsdAmount = $netCryptoAmount * $cryptoUsdRate;
        $feeUsdAmount = $feeInCrypto * $cryptoUsdRate;

        // The user receives Naira based on the net crypto amount
        $nairaAmount = $netUsdAmount * $usdNgnRate;

        // If it's a gaspump currency, we need to move the crypto on-chain
        if ($cryptoWallet->currency->is_gaspump) {
            $gasWallet = SystemWallet::where('currency_id', $cryptoWallet->currency_id)
                ->where('type', SystemWalletType::GAS)
                ->firstOrFail();

            if ($cryptoWallet->status === WalletStatus::PENDING) {
                $this->gaspumpService->activateAddress(
                    $cryptoWallet,
                    $cryptoWallet->currency,
                    $cryptoWallet->currency->hdWallet,
                    $gasWallet
                );

                $cryptoWallet->status = WalletStatus::ACTIVE;
                $cryptoWallet->save();

                throw new Exception('Wallet not activated, please retry in 5 minutes', 400);
            }
        }

        DB::transaction(function () use ($cryptoWallet, $nairaWallet, $amount, $netCryptoAmount, $feeInCrypto, $netUsdAmount, $feeUsdAmount, $nairaAmount, $cryptoUsdRate, $usdNgnRate, $feePercentage) {
            // Lock wallets for the transaction
            $lockedCryptoWallet = Wallet::where('id', $cryptoWallet->id)->lockForUpdate()->firstOrFail();
            $lockedNairaWallet = Wallet::where('id', $nairaWallet->id)->lockForUpdate()->firstOrFail();
            $lockedSystemWallet = SystemWallet::where('currency_id', $cryptoWallet->currency_id)->where('type', SystemWalletType::SELL)->lockForUpdate()->firstOrFail();
            $lockedSystemFeeWallet = SystemWallet::where('currency_id', $cryptoWallet->currency_id)->where('type', SystemWalletType::FEE)->lockForUpdate()->first();

            $gasWallet = SystemWallet::where('currency_id', $cryptoWallet->currency_id)->where('type', SystemWalletType::GAS)->firstOrFail();

            if ($lockedSystemWallet == null) {
                throw new Exception('Sell not configured for this currency.', 500);
            }

            if ($lockedSystemFeeWallet == null) {
                throw new Exception('Fee wallet not configured', 500);
            }

            if ($lockedCryptoWallet->balance < $amount) {
                throw new Exception('Insufficient balance during transaction.', 400);
            }

            $reference = 'SELL-' . strtoupper(bin2hex(random_bytes(8)));

            $metadata = [
                'crypto_usd_rate' => $cryptoUsdRate,
                'usd_ngn_rate' => $usdNgnRate,
                'fee_percentage' => $feePercentage,
            ];

            // If it's a gaspump currency, we need to move the crypto on-chain
            if ($cryptoWallet->currency->is_gaspump) {
                // For gaspump, we transfer the total amount (net + fee) to the system SELL wallet
                $signatureId = $this->gaspumpService->multipleTransfer(
                    $lockedCryptoWallet,
                    [$lockedSystemWallet->address, $lockedSystemFeeWallet->address],
                    [(string) $netCryptoAmount, (string) $feeInCrypto],
                    $gasWallet,
                    $cryptoWallet->currency,
                    $cryptoWallet->currency->hdWallet
                );
                $metadata['signatureId'] = $signatureId;
            }

            // 1. Debit User Crypto Wallet for the net amount going to the SELL wallet
            $this->ledgerService->recordWithdrawal(
                $lockedCryptoWallet,
                $lockedSystemWallet,
                $netCryptoAmount,
                $netUsdAmount,
                $reference,
                "Sold {$netCryptoAmount} {$cryptoWallet->currency->symbol} for NGN",
                $metadata,
                'completed',
                'sell'
            );

            // 2. Debit User Crypto Wallet for the fee going to the FEE wallet
            $this->ledgerService->recordFee(
                $lockedCryptoWallet,
                $lockedSystemFeeWallet,
                $feeInCrypto,
                $feeUsdAmount,
                $reference,
                "Fee for selling {$cryptoWallet->currency->symbol}",
                $metadata
            );

            // 3. Credit User Naira Wallet
            $this->ledgerService->recordDeposit(
                null,
                $lockedNairaWallet,
                $nairaAmount,
                $netUsdAmount,
                $reference,
                "Received NGN from selling {$cryptoWallet->currency->symbol}",
                $metadata,
                'completed',
                'sell'
            );
        });

        return [
            'success' => true,
            'message' => 'Successfully sold crypto for NGN.',
            'naira_amount' => $nairaAmount,
            'net_crypto_amount' => $netCryptoAmount,
            'fee_crypto_amount' => $feeInCrypto,
            'usd_amount' => $netUsdAmount,
        ];
    }
}
