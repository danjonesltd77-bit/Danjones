<?php

namespace App\Domains\Wallet\Services;

use App\Domains\Wallet\Contracts\WalletAccountInterface;
use App\Domains\Wallet\Contracts\TransactionRepositoryInterface;
use App\Domains\Wallet\Models\Transaction;
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
        float $usdAmount,
        string $reference,
        string $description,
        array $metadata = [],
        string $status = 'completed'
    ): void {
        DB::transaction(function () use ($systemWallet, $userWallet, $amount, $usdAmount, $reference, $description, $metadata, $status) {
            $this->repository->recordEntry($systemWallet, $amount, $usdAmount, 'debit', 'deposit', $reference, $description, $metadata, $status);
            $this->repository->recordEntry($userWallet, $amount, $usdAmount, 'credit', 'deposit', $reference, $description, $metadata, $status);
        });
    }

    /**
     * Records a withdrawal which involves a debit to the user's wallet and a credit to a system wallet.
     */
    public function recordWithdrawal(
        WalletAccountInterface $userWallet,
        WalletAccountInterface $systemWallet,
        float $amount,
        float $usdAmount,
        string $reference,
        string $description,
        array $metadata = [],
        string $status = 'completed'
    ): void {
        DB::transaction(function () use ($userWallet, $systemWallet, $amount, $usdAmount, $reference, $description, $metadata, $status) {
            $this->repository->recordEntry($userWallet, $amount, $usdAmount, 'debit', 'withdrawal', $reference, $description, $metadata, $status);
            $this->repository->recordEntry($systemWallet, $amount, $usdAmount, 'credit', 'withdrawal', $reference, $description, $metadata, $status);
        });
    }

    /**
     * Records a fee which involves a debit to the user's wallet and a credit to a system fee wallet.
     */
    public function recordFee(
        WalletAccountInterface $userWallet,
        WalletAccountInterface $systemFeeWallet,
        float $amount,
        float $usdAmount,
        string $reference,
        string $description,
        array $metadata = [],
        string $status = 'completed'
    ): void {
        DB::transaction(function () use ($userWallet, $systemFeeWallet, $amount, $usdAmount, $reference, $description, $metadata, $status) {
            $this->repository->recordEntry($userWallet, $amount, $usdAmount, 'debit', 'fee', $reference, $description, $metadata, $status);
            $this->repository->recordEntry($systemFeeWallet, $amount, $usdAmount, 'credit', 'fee', $reference, $description, $metadata, $status);
        });
    }

    /**
     * Updates the status of all transactions associated with a given reference.
     * Use this to move double-entry pairs from "pending" to "completed".
     */
    public function updateTransactionStatus(string $reference, string $newStatus): void
    {
        DB::transaction(function () use ($reference, $newStatus) {
            $transactions = Transaction::where('reference', $reference)->get();

            foreach ($transactions as $transaction) {
                $this->repository->updateTransactionStatus($transaction, $newStatus);
            }
        });
    }
}
