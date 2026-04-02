<?php

namespace App\Livewire\Admin\Transactions;

use Livewire\Component;

class TransactionsManagement extends Component
{
    public function render()
    {
        return view('livewire.admin.transactions.transactions-management')
            ->layout('layouts.app');
    }
}

