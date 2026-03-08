<?php

namespace App\Domains\Wallet\Contracts;
use App\Domains\Wallet\Models\Transaction;

interface TransactionRepositoryInterface
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
    ): Transaction;

    public function updateTransactionStatus(
        Transaction $transaction,
        string $newStatus
    ): void;
}
