<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Core\Services\SettingService;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\OnchainSend;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\SystemWalletType;
use Illuminate\Support\Facades\Log;

class SendOnchainAction
{
    public function __construct(
        protected CryptoGatewayInterface $cryptoGateway,
        protected MarketDataGatewayInterface $marketDataGateway,
        protected SettingService $settingService
    ) {}

    /**
     * Execute the on-chain send process for Bitcoin/UTXO based coins.
     */
    public function execute(float $amount, int $currencyId, string $recipientAddress): array
    {
        $currency = Currency::with('hdWallet')->findOrFail($currencyId);
        $amount = round($amount, 6);

        // 1. Get Change Wallet from SystemWallets
        $changeWallet = SystemWallet::where('currency_id', $currencyId)
            ->where('type', SystemWalletType::CHANGE)
            ->first();

        $walletsToSelectFrom = collect();

        // Add change wallet to selection if balance > 0
        if ($changeWallet) {
            $onchainBalance = $this->cryptoGateway->getBalance($changeWallet->address, $currency);
            if ($onchainBalance > 0) {
                // Mock a wallet object for selection logic
                $walletsToSelectFrom->push((object) [
                    'address' => $changeWallet->address,
                    'address_balance' => $onchainBalance,
                    'index' => 0,
                    'is_system' => true,
                ]);
            }
        }

        // 2. Get DB user wallets with on-chain balance
        $dbWallets = Wallet::where('currency_id', $currencyId)
            ->where('address_balance', '>', 0)
            ->get();

        foreach ($dbWallets as $wallet) {
            $walletsToSelectFrom->push((object) [
                'id' => $wallet->id,
                'address' => $wallet->address,
                'address_balance' => (float) $wallet->address_balance,
                'index' => (int) $wallet->index,
                'is_system' => false,
                'model' => $wallet,
            ]);
        }

        // 3. Selection Logic (Sorting by descending balance to minimize inputs)
        $sortedWallets = $walletsToSelectFrom->sortByDesc('address_balance');
        $selectedWallets = collect();
        $currentBalance = 0;

        foreach ($sortedWallets as $wallet) {
            $selectedWallets->push($wallet);
            $currentBalance += $wallet->address_balance;
            if ($currentBalance >= $amount) {
                break;
            }
        }

        if ($currentBalance < $amount) {
            Log::alert('Insufficient on-chain balance for broadcast', [
                'total' => $currentBalance,
                'requested' => $amount,
            ]);
            throw new \Exception('Service not available');
        }

        // 4. Fee Estimation
        $hdWallet = $currency->hdWallet;
        if (! $hdWallet) {
            throw new \Exception('HD Wallet configuration not found for this currency.');
        }

        try {
            $fee = $this->cryptoGateway->estimateOnchainFee($currency, $amount);

            // Fee Validation vs USD threshold
            $rate = $this->marketDataGateway->getExchangeRate($currencyId);
            $feeUsd = $fee * $rate;
            $maxFeeUsd = $this->settingService->get('onchain_fee_max_usd', 50.0);

            if ($feeUsd > $maxFeeUsd) {
                Log::warning('On-chain fee exceeded maximum threshold', [
                    'fee_usd' => $feeUsd,
                    'max_usd' => $maxFeeUsd,
                ]);
                $this->settingService->set('last_high_fee_usd', $feeUsd);
                throw new \Exception("Transaction fee too high: {$feeUsd} USD. (Max allowed: {$maxFeeUsd} USD)");
            }

            // Ensure balance covers amount + fee
            if ($currentBalance < ($amount + $fee)) {
                throw new \Exception('Insufficient balance to cover the transaction amount plus fee.');
            }

        } catch (\Exception $e) {
            Log::error('On-chain fee estimation failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        // 5. Build Signature Map and Broadcast
        $signatureId = $hdWallet->signature_id;
        $formattedWallets = $selectedWallets->map(function ($wallet) use ($signatureId) {
            return [
                'address' => $wallet->address,
                'signatureId' => $signatureId,
                'index' => (int) $wallet->index,
            ];
        })->values()->toArray();

        try {
            $res = $this->cryptoGateway->utxoSend(
                $currency,
                $formattedWallets,
                $recipientAddress,
                $amount,
                $fee,
                $changeWallet?->address
            );

            // 6. Finalize: Create Record and Zero Out DB Wallets
            OnchainSend::create([
                'signatureId' => $res['signatureId'] ?? null,
                'txid' => $res['txId'] ?? null,
                'sender_address' => $selectedWallets->pluck('address')->toArray(),
                'recipient_address' => $recipientAddress,
                'amount' => ($amount - $fee),
                'fee' => $fee,
                'rate' => $rate,
                'currency_id' => $currencyId,
                'status' => 'pending',
            ]);

            foreach ($selectedWallets as $wallet) {
                if ($wallet->is_system) {
                    continue;
                }
                $wallet->model->update(['address_balance' => 0]);
            }

            return $res;
        } catch (\Exception $e) {
            Log::error('Tatum on-chain broadcast failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
