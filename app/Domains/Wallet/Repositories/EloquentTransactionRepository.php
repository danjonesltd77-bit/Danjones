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
        string $type,
        string $reference,
        string $description,
        array $metadata = []
    ): void {
        Transaction::create([
            'user_id' => $account->getUserId(),
            'wallet_id' => $account->getWalletId(),
            'currency_id' => $account->getCurrencyId(),
            'amount' => $amount,
            'type' => $type,
            'previous_balance' => 0, // TODO: Implement balance tracking
            'current_balance' => 0,  // TODO: Implement balance tracking
            'reference' => $reference,
            'description' => $description,
            'metadata' => json_encode($metadata),
        ]);
    }
}
