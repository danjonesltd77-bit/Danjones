<?php

namespace App\Domains\Wallet\Repositories;

use App\Domains\Wallet\Contracts\WalletAccountInterface;
use App\Domains\Wallet\Contracts\TransactionRepositoryInterface;
use App\Domains\Wallet\Models\Transaction;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function recordEntry(
        WalletAccountInterface $account,
        float $amount,
        float $usdAmount,
        string $type,
        string $action,
        string $reference,
        string $description,
        array $metadata = [],
        string $status = 'completed'
    ): Transaction {
        $walletClass = $account->getWalletType();
        $walletModel = $walletClass::where('id', $account->getWalletId())->lockForUpdate()->firstOrFail();

        $previousBalance = (float) $walletModel->balance;
        $currentBalance = $previousBalance;

        if ($status !== 'pending') {
            if ($type === 'credit') {
                $currentBalance = $previousBalance + $amount;
            } else {
                // debit or fee (usually subtract)
                $currentBalance = $previousBalance - $amount;
            }

            $walletModel->update(['balance' => $currentBalance]);
        }

        return Transaction::create([
            'user_id' => $account->getUserId(),
            'wallet_id' => $account->getWalletId(),
            'wallet_type' => $account->getWalletType(),
            'currency_id' => $account->getCurrencyId(),
            'action' => $action,
            'amount' => $amount,
            'usd' => $usdAmount,
            'type' => $type,
            'previous_balance' => $previousBalance,
            'current_balance' => $currentBalance,
            'reference' => $reference,
            'description' => $description,
            'metadata' => $metadata, // casted to array in model, so pass array
            'status' => $status,
        ]);
    }

    public function updateTransactionStatus(Transaction $transaction, string $newStatus): void
    {
        if ($transaction->status->value === $newStatus) {
            return;
        }

        if ($transaction->status->value === 'pending' && $newStatus === 'completed') {
            $walletClass = $transaction->wallet_type;
            $walletModel = $walletClass::where('id', $transaction->wallet_id)->lockForUpdate()->firstOrFail();

            $previousBalance = (float) $walletModel->balance;
            $amount = (float) $transaction->amount;

            if ($transaction->type->value === 'credit') {
                $currentBalance = $previousBalance + $amount;
            } else {
                $currentBalance = $previousBalance - $amount;
            }

            $walletModel->update(['balance' => $currentBalance]);

            $transaction->update([
                'status' => $newStatus,
                'previous_balance' => $previousBalance,
                'current_balance' => $currentBalance,
            ]);
        } else {
            $transaction->update(['status' => $newStatus]);
        }
    }
}
