<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Core\Services\SettingService;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\GaspumpServiceInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\OnchainSend;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\SystemWalletType;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendAction
{
    public function __construct(
        protected CryptoGatewayInterface $cryptoGateway,
        protected LedgerService $ledgerService,
        protected MarketDataGatewayInterface $marketDataGateway,
        protected SettingService $settingService
    ) {}

    public function execute(User $user, int $currencyId, float $amount, string $recipientAddress): array
    {
        $currency = Currency::with('hdWallet')->findOrFail($currencyId);
        $wallet = $user->wallet($currencyId);

        if (!$currency->is_crypto) {
            throw new Exception('Currency is not crypto');
        }

        if (! $wallet) {
            throw new Exception('Wallet not found for this currency.', 404);
        }

        $amount = round($amount, 6);

        // 1. Get total fee (Network + Service) from gateway estimation
        $totalFee = $this->cryptoGateway->estimateOnchainFee($currency, $amount);
        $serviceFee = (float) $this->settingService->get('send_fee_'.Str::lower($currency->symbol), $currency->fee);

        // For UTXO, networkFee is totalFee - serviceFee. For Gaspump, totalFee IS the serviceFee.
        $networkFee = $currency->is_gaspump ? 0 : max(0, $totalFee - $serviceFee);

        if ($wallet->balance < ($amount + $totalFee)) {
            throw new Exception('Insufficient balance to cover amount plus fees.', 400);
        }

        $rate = $this->marketDataGateway->getExchangeRate($currencyId);
        $feeWallet = SystemWallet::where('currency_id', $currencyId)
            ->where('type', SystemWalletType::FEE)
            ->first();

        if (! $feeWallet) {
            throw new Exception('System fee wallet not configured for this currency.', 500);
        }

        return DB::transaction(function () use ($user, $currency, $wallet, $amount, $recipientAddress, $totalFee, $serviceFee, $networkFee, $feeWallet, $rate) {
            // Re-fetch and lock for update to ensure balance hasn't changed
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            if ($lockedWallet->balance < ($amount + $totalFee)) {
                throw new Exception('Insufficient balance during transaction processing.', 400);
            }

            // 2. Perform On-chain Transaction
            if ($currency->is_gaspump) {
                $res = $this->handleGaspumpSend($user, $currency, $lockedWallet, $amount, $recipientAddress, $serviceFee, $feeWallet);
            } else {
                $res = $this->handleUtxoSend($currency, $amount, $recipientAddress, $networkFee);
            }

            // 3. Record Ledger Entries
            // Debit user for the base amount and network/gas cost
            $this->ledgerService->recordWithdrawal(
                $lockedWallet,
                null,
                ($amount + $networkFee),
                ($amount + $networkFee) * $rate,
                $res['txId'] ?? $res['signatureId'] ?? Str::random(16),
                "Transfer to {$recipientAddress}"
            );

            // Record the Service Fee (debit user, credit system fee wallet)
            $this->ledgerService->recordFee(
                $lockedWallet,
                $feeWallet,
                $serviceFee,
                $serviceFee * $rate,
                $res['txId'] ?? $res['signatureId'] ?? Str::random(16),
                'Transaction Service Fee'
            );

            // 4. Record Audit Entry
            OnchainSend::create([
                'signatureId' => $res['signatureId'] ?? null,
                'txid' => $res['txId'] ?? null,
                'sender_address' => [$currency->is_gaspump ? $wallet->address : 'UTXO_MULTIPLE'],
                'recipient_address' => $recipientAddress,
                'amount' => $amount,
                'fee' => $totalFee,
                'rate' => $rate,
                'currency_id' => $currency->id,
                'status' => 'pending',
            ]);

            return $res;
        });
    }

    protected function handleGaspumpSend(User $user, Currency $currency, Wallet $wallet, float $amount, string $recipientAddress, float $serviceFee, SystemWallet $feeWallet): array
    {
        $gaspump = app(GaspumpServiceInterface::class);

        $gasWallet = SystemWallet::where('currency_id', $currency->id)
            ->where('type', SystemWalletType::GAS)
            ->first();

        if (! $gasWallet) {
            throw new Exception('Gas wallet not configured for this currency.', 500);
        }

        $txId = $gaspump->gaspumpBatchTransfer(
            $wallet,
            [$recipientAddress, $feeWallet->address],
            [(string) $amount, (string) $serviceFee],
            $gasWallet,
            $currency,
            $currency->hdWallet
        );

        return ['txId' => $txId];
    }

    protected function handleUtxoSend(Currency $currency, float $amount, string $recipientAddress, float $networkFee): array
    {
        $res = $this->cryptoGateway->utxoSend(
            $currency,
            $recipientAddress,
            $amount,
            $networkFee
        );

        if (! empty($res['spentAddresses'])) {
            Wallet::whereIn('address', $res['spentAddresses'])
                ->where('currency_id', $currency->id)
                ->lockForUpdate()
                ->update(['address_balance' => 0]);
        }

        return $res;
    }
}
