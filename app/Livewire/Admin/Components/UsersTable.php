<?php

namespace App\Livewire\Admin\Components;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class UsersTable extends Component
{
    public int $limit = 5;

    #[Computed]
    public function users()
    {
        return User::latest()->take($this->limit)->get();
    }

    public function render()
    {
        return view('livewire.admin.components.users-table');
    }
}
