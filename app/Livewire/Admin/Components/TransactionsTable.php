<?php

namespace App\Livewire\Admin\Components;

use App\Domains\Wallet\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsTable extends Component
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
    public function transactions()
    {
        $query = Transaction::with(['user', 'currency'])->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('amount', 'like', '%' . $this->search . '%')
                    ->orWhere('type', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($qu) {
                        $qu->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->paginated) {
            return $query->paginate($this->limit);
        }

        return $query->take($this->limit)->get();
    }

    public function render()
    {
        return view('livewire.admin.components.transactions-table');
    }
}
