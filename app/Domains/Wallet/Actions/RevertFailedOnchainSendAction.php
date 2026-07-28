<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Contracts\TransactionRepositoryInterface;
use App\Domains\Wallet\Models\OnchainSend;
use App\Domains\Wallet\Models\Transaction;
use App\Enum\TransactionStatus;
use Illuminate\Support\Facades\DB;

class RevertFailedOnchainSendAction
{
    public function __construct(
        protected TransactionRepositoryInterface $repository
    ) {}

    /**
     * Reverts all completed ledger transactions associated with a transaction's reference.
     */
    public function execute(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $reference = $transaction->reference;

            $transactions = Transaction::where('reference', $reference)->get();

            // Check if already reverted/refunded
            if ($transactions->contains(fn ($tx) => $tx->status === TransactionStatus::FAILED || $tx->status === TransactionStatus::REFUNDED)) {
                throw new \Exception('This transaction has already been reverted or refunded.');
            }

            foreach ($transactions as $tx) {
                // Determine the reverse double-entry type
                $reverseType = $tx->type->value === 'debit' ? 'credit' : 'debit';

                $walletClass = $tx->wallet_type;
                $walletModel = $walletClass::findOrFail($tx->wallet_id);

                $this->repository->recordEntry(
                    $walletModel,
                    $tx->amount,
                    $tx->usd,
                    $reverseType,
                    'refund',
                    'refund_'.$tx->reference,
                    "Refund/Reversal for transaction #{$tx->id} ({$tx->description})",
                    ['original_transaction_id' => $tx->id],
                    'completed'
                );

                // Update original transaction status
                $tx->update(['status' => TransactionStatus::REFUNDED]);
            }

            // Update OnchainSend status if exists
            OnchainSend::where('txid', $reference)
                ->orWhere('signatureId', $reference)
                ->update(['status' => 'failed']);
        });
    }
}
