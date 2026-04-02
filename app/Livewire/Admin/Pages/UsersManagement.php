<?php

namespace App\Livewire\Admin\Pages;

use Livewire\Component;

class UsersManagement extends Component
{
    public function render()
    {
        return view('livewire.admin.pages.users-management')
            ->layout('layouts.app');
    }
}
