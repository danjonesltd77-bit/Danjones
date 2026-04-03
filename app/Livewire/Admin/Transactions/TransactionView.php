<?php

namespace App\Livewire\Admin\Transactions;

use App\Domains\Wallet\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TransactionView extends Component
{
    public Transaction $transaction;

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction->load(['user', 'wallet.currency', 'currency']);
    }

    #[Computed]
    public function relatedTransactions()
    {
        $transactions = Transaction::query()
            ->with(['currency', 'user'])
            ->where('reference', $this->transaction->reference)
            ->where('id', '!=', $this->transaction->id)
            ->get();

        return [
            'system' => $transactions->filter(fn ($tx) => $tx->user_id === 0),
            'user' => $transactions->filter(fn ($tx) => $tx->user_id > 0),
        ];
    }

    public function render()
    {
        return view('livewire.admin.transactions.transaction-view');
    }
}
