<?php

namespace App\Domains\Wallet\Services;

use App\Domains\Wallet\Contracts\WalletAccountInterface;
use App\Domains\Wallet\Contracts\TransactionRepositoryInterface;
use App\Enum\TransactionAction;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Enum\TransactionType;

class LedgerService
{
    protected TransactionRepositoryInterface $repository;

    public function __construct(TransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Records a deposit which involves a debit from a system wallet and a credit to the user's wallet.
     * In a deposit: SystemWallet (assets) balance increases but they are viewed as liabilities for users.
     * Wait, from System's context, an incoming deposit on chain means System Wallet gets DEBIT (increases asset),
     * and User Wallet gets CREDIT (increases liability/balance).
     */
    public function recordDeposit(
        WalletAccountInterface $systemWallet,
        WalletAccountInterface $userWallet,
        float $amount,
        string $reference,
        string $description,
        array $metadata = []
    ): void {
        DB::transaction(function () use ($systemWallet, $userWallet, $amount, $reference, $description, $metadata) {
            $this->repository->recordEntry($systemWallet, $amount, 'debit', 'deposit', $reference, $description, $metadata);
            $this->repository->recordEntry($userWallet, $amount, 'credit', 'deposit', $reference, $description, $metadata);
        });
    }

    /**
     * Records a withdrawal which involves a debit to the user's wallet and a credit to a system wallet.
     */
    public function recordWithdrawal(
        WalletAccountInterface $userWallet,
        WalletAccountInterface $systemWallet,
        float $amount,
        string $reference,
        string $description,
        array $metadata = []
    ): void {
        DB::transaction(function () use ($userWallet, $systemWallet, $amount, $reference, $description, $metadata) {
            $this->repository->recordEntry($userWallet, $amount, 'debit', 'withdrawal', $reference, $description, $metadata);
            $this->repository->recordEntry($systemWallet, $amount, 'credit', 'withdrawal', $reference, $description, $metadata);
        });
    }

    /**
     * Records a fee which involves a debit to the user's wallet and a credit to a system fee wallet.
     */
    public function recordFee(
        WalletAccountInterface $userWallet,
        WalletAccountInterface $systemFeeWallet,
        float $amount,
        string $reference,
        string $description,
        array $metadata = []
    ): void {
        DB::transaction(function () use ($userWallet, $systemFeeWallet, $amount, $reference, $description, $metadata) {
            $this->repository->recordEntry($userWallet, $amount, 'debit', 'fee', $reference, $description, $metadata);
            $this->repository->recordEntry($systemFeeWallet, $amount, 'credit', 'fee', $reference, $description, $metadata);
        });
    }
}
