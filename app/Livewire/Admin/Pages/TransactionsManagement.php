<?php

namespace App\Livewire\Admin\Pages;

use Livewire\Component;

class TransactionsManagement extends Component
{
    public function render()
    {
        return view('livewire.admin.pages.transactions-management')
            ->layout('layouts.app');
    }
}
