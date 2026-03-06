<?php

namespace App\Domains\Wallet\Contracts;

interface TransactionRepositoryInterface
{
    public function recordEntry(
        WalletAccountInterface $account,
        float $amount,
        string $type,
        string $reference,
        string $description,
        array $metadata = []
    ): void;
}
