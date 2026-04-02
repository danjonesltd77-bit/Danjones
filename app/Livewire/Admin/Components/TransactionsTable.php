<?php

namespace App\Livewire\Admin\Components;

use App\Domains\Wallet\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TransactionsTable extends Component
{
    public int $limit = 5;

    #[Computed]
    public function transactions()
    {
        return Transaction::with(['user', 'currency'])->latest()->take($this->limit)->get();
    }

    public function render()
    {
        return view('livewire.admin.components.transactions-table');
    }
}
