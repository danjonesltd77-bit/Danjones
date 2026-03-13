<?php

namespace App\Domains\Bank\Actions;

use App\Domains\Bank\Models\BankAccount;
use App\Models\User;

class ManageBankAccountAction
{
    /**
     * Add a new bank account for a user.
     */
    public function addAccount(User $user, array $data): BankAccount
    {
        return $user->bankAccounts()->create([
            'bank_id' => $data['bank_id'],
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'],
            'is_active' => true,
        ]);
    }

    /**
     * Delete a bank account for a user.
     */
    public function deleteAccount(User $user, BankAccount $bankAccount): void
    {
        if ($bankAccount->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $bankAccount->delete();
    }
}
