<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;

class UsersManagement extends Component
{
    public function render()
    {
        return view('livewire.admin.users.users-management')
            ->layout('layouts.app');
    }
}

