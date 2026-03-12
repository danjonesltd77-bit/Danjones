<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Core\Services\SettingService;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\SystemWalletType;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class SellAction
{
    public function __construct(
        private MarketDataGatewayInterface $marketDataGateway,
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

        DB::transaction(function () use ($cryptoWallet, $nairaWallet, $amount, $netCryptoAmount, $feeInCrypto, $netUsdAmount, $feeUsdAmount, $nairaAmount, $cryptoUsdRate, $usdNgnRate) {
            // Lock wallets for the transaction
            $lockedCryptoWallet = Wallet::where('id', $cryptoWallet->id)->lockForUpdate()->firstOrFail();
            $lockedNairaWallet = Wallet::where('id', $nairaWallet->id)->lockForUpdate()->firstOrFail();
            $lockedSystemWallet = SystemWallet::where('currency_id', $cryptoWallet->currency_id)->where('type', SystemWalletType::SELL)->lockForUpdate()->firstOrFail();
            $lockedSystemFeeWallet = SystemWallet::where('currency_id', $cryptoWallet->currency_id)->where('type', SystemWalletType::FEE)->lockForUpdate()->first();

            if ($lockedSystemWallet == null) {
                throw new Exception('Sell not configured for this currency.', 500);
            }

            if ($lockedSystemFeeWallet == null) {
                throw new Exception('Fee wallet not configured', 500);
            }

            if ($lockedCryptoWallet->balance < $amount) {
                throw new Exception('Insufficient balance during transaction.', 400);
            }

            $reference = 'SELL-'.strtoupper(bin2hex(random_bytes(8)));

            // 1. Debit User Crypto Wallet for the net amount going to the SELL wallet
            $this->ledgerService->recordWithdrawal(
                $lockedCryptoWallet,
                $lockedSystemWallet,
                $netCryptoAmount,
                $netUsdAmount,
                $reference,
                "Sold {$netCryptoAmount} {$cryptoWallet->currency->symbol} for NGN",
                ['rate' => $cryptoUsdRate]
            );

            // 2. Debit User Crypto Wallet for the fee going to the FEE wallet
            $this->ledgerService->recordFee(
                $lockedCryptoWallet,
                $lockedSystemFeeWallet,
                $feeInCrypto,
                $feeUsdAmount,
                $reference,
                "Fee for selling {$cryptoWallet->currency->symbol}",
                ['rate' => $cryptoUsdRate]
            );

            // 3. Credit User Naira Wallet
            // Since recordDeposit takes usdAmount, we pass the netUsdAmount.
            // But we need the fiat amount to be accurately recorded in 'amount' column for Naira.
            $this->ledgerService->recordDeposit(
                null, // System wallet could be added here if needed for liquidity
                $lockedNairaWallet,
                $nairaAmount,
                $netUsdAmount,
                $reference,
                "Received NGN from selling {$cryptoWallet->currency->symbol}",
                ['usd_ngn_rate' => $usdNgnRate]
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
