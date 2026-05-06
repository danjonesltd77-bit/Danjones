<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Services\LedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $address = $payload['address'] ?? $payload['to'] ?? null;

        $currencyId = Currency::where('token_currency', $payload['currency'])->first()->id;

        if (isset($payload['tokenMetadata']) && isset($payload['contractAddress']) && $payload['tokenMetadata']['type'] === 'fungible') {
            $currencyId = Currency::where('token_address', $payload['contractAddress'])->first()->id;
        }

        if (! $txHash || ! $address) {
            Log::warning('Subscription missing required fields', ['payload' => $payload]);

            return ['success' => false, 'message' => 'Invalid subscription payload.'];
        }

        $wallet = Wallet::with(['user', 'currency'])->where('address', $address)->where('currency_id', $currencyId)->first();
        if (! $wallet) {
            Log::warning('Subscription wallet not found', ['address' => $address, 'txHash' => $txHash]);

            return ['success' => false, 'message' => 'Wallet not found.'];
        }

        $details = $this->cryptoGateway->getTransactionDetails($txHash, $wallet->currency);

        // General validity check for all currencies
        if (! $details || ! ($details['blockNumber'] ?? $details['status'] ?? false)) {
            return ['success' => false, 'message' => 'Transaction not found or not yet confirmed.'];
        }

        // Determine amount and status based on currency
        [$amount, $status] = match ($wallet->currency_id) {
            2 => [$this->extractBitcoinAmount($details, $wallet->address), 'pending'],
            5 => [$this->extractBitcoinAmount($details, $wallet->address), 'pending'],
            3 => [$this->extractTronAmount($details, $wallet->address), 'completed'],
            4 => [$this->extractTrc20Amount($details, $wallet->address), 'completed'],
            7 => [$this->extractEthereumAmount($details), 'completed'],
            default => [$this->extractGenericAmount($details), 'pending'],
        };

        return $this->processValidatedDeposit($wallet, $amount, $txHash, $payload, $status);
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

            // Fetch on-chain balance immediately after deposit
            \App\Domains\Wallet\Jobs\UpdateAddressBalanceJob::dispatch($lockedWallet);
        });

        if ($alreadyProcessed) {
            return ['success' => true, 'message' => 'Deposit already processed.'];
        }

        return [
            'success' => true,
            'message' => 'Deposit processed successfully.',
            'amount' => $verifiedAmount,
            'txHash' => $txHash,
        ];
    }

    /**
     * Extract Bitcoin amount from UTXO outputs.
     */
    private function extractBitcoinAmount(array $details, string $address): float
    {
        $outputs = $details['outputs'] ?? [];
        if (! is_array($outputs) || empty($outputs)) {
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
        if (! is_array($contracts)) {
            return 0;
        }

        $sun = 0;
        foreach ($contracts as $contract) {
            $value = $contract['parameter']['value'] ?? [];

            // Native TRX transfer or TRC10
            if (isset($value['toAddressBase58']) && $value['toAddressBase58'] === $address) {
                $sun += (float) ($value['amount'] ?? 0);
            }
        }

        return $sun > 0 ? $sun / 1000000 : 0;
    }

    /**
     * Extract Ethereum amount from transaction details.
     * Handles both Tatum's parsed 'amount' and raw 'value' (Wei).
     */
    private function extractEthereumAmount(array $details): float
    {
        // If Tatum already parsed the amount into ETH
        if (isset($details['amount'])) {
            return (float) $details['amount'];
        }

        // If we have raw 'value' in Wei (standard for Ethereum JSON-RPC responses)
        if (isset($details['value'])) {
            $value = $details['value'];

            // Convert Wei to ETH (18 decimals)
            // Using bcdiv if available for precision, or standard float division
            if (is_numeric($value)) {
                return (float) $value / 1000000000000000000;
            }
        }

        return 0.0;
    }

    /**
     * Fallback for Generic amounts (EVM/Tokens) where Tatum provides top-level amount.
     */
    private function extractGenericAmount(array $details): float
    {
        return (float) ($details['amount'] ?? 0);
    }

    /**
     * Extract TRC20 (USDT) amount from TRON logs and validate recipient.
     */
    private function extractTrc20Amount(array $details, string $address): float
    {
        $logs = $details['log'] ?? [];
        $transferTopic = 'ddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';

        // Convert the Base58 address to its core hex representation for comparison
        $expectedHex = $this->tronAddressToHex($address);

        foreach ($logs as $log) {
            $topics = $log['topics'] ?? [];
            if (empty($topics) || $topics[0] !== $transferTopic) {
                continue;
            }

            // In TRC20 Transfer(address,address,uint256), the recipient is in topics[2]
            // It's a 32-byte hex, left-padded with zeros. Core address is the last 40 chars.
            $recipientHex = isset($topics[2]) ? substr($topics[2], -40) : null;

            // Verify that the recipient in the log matches our wallet address
            if ($recipientHex !== $expectedHex) {
                continue;
            }

            $data = $log['data'] ?? '0';
            $rawAmount = hexdec(ltrim($data, '0'));

            // USDT usually has 6 decimals
            return $rawAmount / 1000000;
        }

        return 0.0;
    }

    /**
     * Convert TRON Base58 address to its core hex representation (20 bytes).
     */
    private function tronAddressToHex(string $address): ?string
    {
        try {
            $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
            $num = gmp_init(0);

            foreach (str_split($address) as $char) {
                $pos = strpos($alphabet, $char);
                if ($pos === false) {
                    return null;
                }
                $num = gmp_add(gmp_mul($num, 58), $pos);
            }

            $hex = gmp_strval($num, 16);
            if (strlen($hex) % 2 !== 0) {
                $hex = '0'.$hex;
            }

            // A full decoded TRON address is 25 bytes (50 hex chars):
            // 1 byte version (0x41) + 20 bytes address + 4 bytes checksum
            $hex = str_pad($hex, 50, '0', STR_PAD_LEFT);

            // Return only the 20-byte address part (skip version and skip checksum)
            return substr($hex, 2, 40);
        } catch (\Exception $e) {
            return null;
        }
    }
}
