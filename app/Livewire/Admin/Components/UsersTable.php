<?php

namespace App\Livewire\Admin\Components;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public int $limit = 5;
    public bool $paginated = false;
    public bool $showSearch = false;
    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        $query = User::latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->paginated) {
            return $query->paginate($this->limit);
        }

        return $query->take($this->limit)->get();
    }

    public function render()
    {
        return view('livewire.admin.components.users-table');
    }
}
