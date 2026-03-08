<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Services\LedgerService;
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

        $currency = $wallet->currency;
        $user = $wallet->user;
        

        if ($currency->gaspump == true) {
            return ['success' => false, 'message' => 'Transaction not compatible with this wallet'];
        }

        if (!$currency || !$user) {
            Log::warning('Subscription missing wallet relations', ['wallet_id' => $wallet->id, 'txHash' => $txHash]);
            return ['success' => false, 'message' => 'Wallet is not linked properly.'];
        }
        
        $transactionDetails = $this->cryptoGateway->getTransactionDetails($txHash, $currency->id);
        if (!$transactionDetails || !isset($transactionDetails['blockNumber'])) {
            Log::warning('Transaction not found or not yet confirmed', ['txHash' => $txHash, 'currency_id' => $currency->id]);
            return ['success' => false, 'message' => 'Transaction not confirmed.'];
        }

        $verifiedAmount = $this->extractIncomingAmount($transactionDetails, $address, $currency->id);
        if ($verifiedAmount <= 0) {
            Log::warning('Transaction output did not match receiving address', [
                'txHash' => $txHash,
                'address' => $address,
                'currency_id' => $currency->id
            ]);
            return ['success' => false, 'message' => 'No valid incoming amount found for receiving wallet.'];
        }

        $reportedAmount = (float) ($payload['amount'] ?? 0);
        if ($reportedAmount > 0 && abs($reportedAmount - $verifiedAmount) > 0.00000001) {
            Log::warning('Reported amount mismatch', [
                'txHash' => $txHash,
                'reported' => $reportedAmount,
                'verified' => $verifiedAmount,
                'wallet_id' => $wallet->id
            ]);
            return ['success' => false, 'message' => 'Reported amount does not match on-chain amount.'];
        }

        $alreadyProcessed = false;

        DB::transaction(function () use ($wallet, $user, $currency, $txHash, $verifiedAmount, &$alreadyProcessed, $payload) {
            // Lock the wallet for update to prevent race conditions
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $existingDeposit = Transaction::where('user_id', $user->id)
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

            // We need a system wallet to debit from
            // Assuming there is a SystemWallet type for the given currency
            $systemWallet = SystemWallet::where('currency_id', $currency->id)->first();

            if (!$systemWallet) {
                Log::error('System wallet not found for deposit', ['currency_id' => $currency->id]);
                // Depending on requirements, we could either throw an exception or create it on the fly
                throw new \Exception("System wallet not configured for this currency.");
            }

            $ledgerService = app(LedgerService::class);
            $ledgerService->recordDeposit(
                $systemWallet,
                $wallet,
                $verifiedAmount,
                $usd,
                $txHash, // using txHash as reference
                "Deposit of {$verifiedAmount} {$currency->symbol}",
                $payload // metadata
            );

            // TODO: Also create a way to record the ngn/usd rates, possibly in metadata or separate fields if Transaction is updated.
        });

        if ($alreadyProcessed) {
            return ['success' => true, 'message' => 'Deposit already processed.'];
        }

        $title = $wallet->currency->name . ' incoming deposit';
        $message = 'Incoming deposit of ' . $verifiedAmount . ' ' . $wallet->currency->symbol;
        // NotificationController::sendNotification($user->id, $title, $message);

        return [
            'success' => true,
            'message' => 'Deposit processed successfully.',
            'amount' => $verifiedAmount,
            'txHash' => $txHash
        ];
    }

    /**
     * Helper to extract the exact amount sent to our specific address
     * from the transaction details.
     */
    private function extractIncomingAmount(array $transactionDetails, string $address, int $currencyId): float
    {
        if ($currencyId !== 2) {
            return (float) ($transactionDetails['amount'] ?? 0);
        }

        $outputs = $transactionDetails['outputs'] ?? [];
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

        if ($satoshis <= 0) {
            return 0;
        }

        return $satoshis / 100000000;
    }
}
