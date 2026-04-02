<?php

namespace App\Livewire\Admin;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Domains\P2P\Models\P2PTrade;
use App\Domains\Wallet\Models\Transaction;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function totalUsers()
    {
        return User::count();
    }

    #[Computed]
    public function totalTransactions()
    {
        try {
            return Transaction::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    #[Computed]
    public function totalP2PTrades()
    {
        try {
            return P2PTrade::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    #[Computed]
    public function totalP2PAds()
    {
        try {
            return P2PAdvertisement::count();
        } catch (\Exception $e) {
            return 0;
        }
    }



    public function render()
    {
        return view('livewire.admin.dashboard')->layout('layouts.app');
    }
}
