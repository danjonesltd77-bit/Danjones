<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\SystemWalletType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessIncomingWebhookAction
{
    public function __construct(
        private CryptoGatewayInterface $cryptoGateway,
        private MarketDataGatewayInterface $marketDataGateway
    ) {}

    /**
     * Executes the incoming webhook logic.
     * Returns an array containing the status and message.
     */
    public function execute(array $payload): array
    {
        Log::info('Incoming subscription payload', ['payload' => $payload]);

        $txHash = $payload['hash'] ?? $payload['txId'] ?? null;
        $address = $payload['address'] ?? null;

        if (!$txHash || !$address) {
            Log::warning('Subscription missing required fields', ['payload' => $payload]);
            return ['success' => false, 'message' => 'Invalid subscription payload.'];
        }

        $wallet = Wallet::with(['user', 'currency'])->where('address', $address)->first();
        if (!$wallet) {
            Log::warning('Subscription wallet not found', ['address' => $address, 'txHash' => $txHash]);
            return ['success' => false, 'message' => 'Wallet not found.'];
        }

        // Delegate handling to currency-specific logic
        return match ($wallet->currency_id) {
            2 => $this->handleBitcoinDeposit($wallet, $txHash, $payload),
            3 => $this->handleTronDeposit($wallet, $txHash, $payload),
            default => $this->handleGenericDeposit($wallet, $txHash, $payload),
        };
    }

    /**
     * Specialized handler for Bitcoin (ID 2).
     */
    private function handleBitcoinDeposit(Wallet $wallet, string $txHash, array $payload): array
    {
        $details = $this->cryptoGateway->getTransactionDetails($txHash, $wallet->currency);
        if (!$details || !isset($details['blockNumber'])) {
            return ['success' => false, 'message' => 'BTC Transaction not found or not yet confirmed.'];
        }

        $amount = $this->extractBitcoinAmount($details, $wallet->address);

        return $this->processValidatedDeposit($wallet, $amount, $txHash, $payload, 'pending');
    }

    /**
     * Specialized handler for TRON (ID 3).
     */
    private function handleTronDeposit(Wallet $wallet, string $txHash, array $payload): array
    {
        $details = $this->cryptoGateway->getTransactionDetails($txHash, $wallet->currency);

        if (!$details || !isset($details['blockNumber'])) {
            return ['success' => false, 'message' => 'TRON Transaction not found or not yet confirmed.'];
        }

        $amount = $this->extractTronAmount($details, $wallet->address);

        if (isset($payload['amount'])) {
            $payload['amount'] = (float) $payload['amount'] * 1000000;
        }

        return $this->processValidatedDeposit($wallet, $amount, $txHash, $payload, 'completed');
    }

    /**
     * Specialized handler for Generic deposits (EVM, Tokens, etc).
     */
    private function handleGenericDeposit(Wallet $wallet, string $txHash, array $payload): array
    {
        $details = $this->cryptoGateway->getTransactionDetails($txHash, $wallet->currency);
        if (!$details || !isset($details['blockNumber'])) {
            return ['success' => false, 'message' => 'Transaction not found or not yet confirmed.'];
        }

        $amount = $this->extractGenericAmount($details);

        return $this->processValidatedDeposit($wallet, $amount, $txHash, $payload, 'pending');
    }

    /**
     * Core business logic to validate amount and persist to ledger.
     */
    private function processValidatedDeposit(Wallet $wallet, float $verifiedAmount, string $txHash, array $payload, string $status = 'pending'): array
    {
        if ($verifiedAmount <= 0) {
            Log::warning('Zero amount verified for deposit', ['txHash' => $txHash, 'wallet' => $wallet->id]);
            return ['success' => false, 'message' => 'No valid incoming amount found.'];
        }

        $reportedAmount = (float) ($payload['amount'] ?? 0);
        if ($reportedAmount > 0 && abs($reportedAmount - $verifiedAmount) > 0.00000001) {
            Log::warning('Reported amount mismatch', ['reported' => $reportedAmount, 'verified' => $verifiedAmount]);
            return ['success' => false, 'message' => 'Reported amount does not match on-chain amount.'];
        }

        $alreadyProcessed = false;

        DB::transaction(function () use ($wallet, $txHash, $verifiedAmount, &$alreadyProcessed, $payload, $status) {
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $currency = $wallet->currency;

            $existingDeposit = Transaction::where('user_id', $wallet->user_id)
                ->where('currency_id', $currency->id)
                ->where('reference', $txHash)
                ->where('action', 'deposit')
                ->lockForUpdate()
                ->first();

            if ($existingDeposit) {
                $alreadyProcessed = true;
                return;
            }

            $coinUsd = (float) $this->marketDataGateway->getExchangeRate($currency->id);
            $usd = $verifiedAmount * $coinUsd;

            $ledgerService = app(LedgerService::class);
            $ledgerService->recordDeposit(
                null,
                $lockedWallet,
                $verifiedAmount,
                $usd,
                $txHash,
                "Deposit of {$verifiedAmount} {$currency->symbol}",
                $payload,
                $status
            );
        });

        if ($alreadyProcessed) {
            return ['success' => true, 'message' => 'Deposit already processed.'];
        }

        return [
            'success' => true,
            'message' => 'Deposit processed successfully.',
            'amount' => $verifiedAmount,
            'txHash' => $txHash
        ];
    }

    /**
     * Extract Bitcoin amount from UTXO outputs.
     */
    private function extractBitcoinAmount(array $details, string $address): float
    {
        $outputs = $details['outputs'] ?? [];
        if (!is_array($outputs) || empty($outputs)) {
            return 0;
        }

        $satoshis = 0;
        foreach ($outputs as $output) {
            if (($output['address'] ?? null) !== $address) {
                continue;
            }
            $satoshis += (float) ($output['value'] ?? 0);
        }

        return $satoshis > 0 ? $satoshis / 100000000 : 0;
    }

    /**
     * Extract TRON amount from raw transaction data.
     */
    private function extractTronAmount(array $details, string $address): float
    {
        $contracts = $details['rawData']['contract'] ?? [];
        if (!is_array($contracts)) {
            return 0;
        }

        $trx = 0;
        foreach ($contracts as $contract) {
            $value = $contract['parameter']['value'] ?? [];

            // Native TRX transfer or TRC10
            if (isset($value['toAddressBase58']) && $value['toAddressBase58'] === $address) {
                $trx += (float) ($value['amount'] ?? 0);
            }
        }

        return $trx > 0 ? $trx : 0;
    }

    /**
     * Fallback for Generic amounts (EVM/Tokens) where Tatum provides top-level amount.
     */
    private function extractGenericAmount(array $details): float
    {
        return (float) ($details['amount'] ?? 0);
    }
}
