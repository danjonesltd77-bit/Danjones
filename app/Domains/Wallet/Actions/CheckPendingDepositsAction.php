<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\TransactionAction;
use App\Enum\TransactionStatus;
use Illuminate\Support\Facades\Log;

class CheckPendingDepositsAction
{
    public function __construct(private CryptoGatewayInterface $cryptoGateway, private LedgerService $ledgerService)
    {   
    }

    public function execute(): void
    {
        // Find all pending crypto deposits tied to active user wallets. 
        // We only check from the user wallet side to avoid duplicating gateway lookups since double entries share the same reference.
        $pendingDeposits = Transaction::query()
            ->where('status', TransactionStatus::PENDING)
            ->where('action', TransactionAction::DEPOSIT)
            ->where('wallet_type', Wallet::class)
            ->get();

        if ($pendingDeposits->isEmpty()) {
            return;
        }

        foreach ($pendingDeposits as $deposit) {
            try {
                // Ensure the currency is indeed a crypto currency
                $currency = $deposit->wallet->currency;

                if (! $currency || ! $currency->is_crypto) {
                    continue; // Skip fiat processing here
                }

                // Call Gateway Blockchain Confirmation validation
                $isConfirmed = $this->cryptoGateway->isTransactionConfirmed($deposit->reference, $currency);

                if ($isConfirmed) {
                    $this->ledgerService->updateTransactionStatus($deposit->reference, TransactionStatus::COMPLETED->value);
                    Log::info("Pending deposit [{$deposit->reference}] for currency {$currency->symbol} confirmed and marked as completed.");
                }
            } catch (\Exception $e) {
                // Ignore temporary failures and allow the job to try again consecutively
                Log::error("Failed to verify pending deposit [{$deposit->reference}]: {$e->getMessage()}");
            }
        }
    }
}
