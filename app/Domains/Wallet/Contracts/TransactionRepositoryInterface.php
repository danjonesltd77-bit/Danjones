<?php

namespace App\Domains\Wallet\Contracts;

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
    ): \App\Domains\Wallet\Models\Transaction;

    public function updateTransactionStatus(
        \App\Domains\Wallet\Models\Transaction $transaction,
        string $newStatus
    ): void;
}
