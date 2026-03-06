<?php

namespace App\Domains\Wallet\Services;

use App\Domains\Wallet\Contracts\WalletAccountInterface;
use App\Domains\Wallet\Contracts\TransactionRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class WalletTransferService
{
    protected TransactionRepositoryInterface $repository;

    public function __construct(TransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function transfer(
        WalletAccountInterface $from,
        WalletAccountInterface $to,
        float $amount,
        string $description
    ): void {
        if ($from->getCurrencyId() !== $to->getCurrencyId()) {
            throw new Exception("Currency mismatch.", 400);
        }

        $reference = uniqid('TRX-');

        DB::transaction(function () use ($from, $to, $amount, $reference, $description) {
            $this->repository->recordEntry($from, $amount, 'debit', $reference, $description);
            $this->repository->recordEntry($to, $amount, 'credit', $reference, $description);
        });
    }
}
