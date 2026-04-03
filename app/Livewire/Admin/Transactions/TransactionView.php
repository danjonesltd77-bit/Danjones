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
        return Transaction::query()
            ->with(['currency'])
            ->where('reference', $this->transaction->reference)
            ->where('id', '!=', $this->transaction->id)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.transactions.transaction-view');
    }
}
