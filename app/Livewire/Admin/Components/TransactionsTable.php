<?php

namespace App\Livewire\Admin\Components;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Transaction;
use App\Enum\TransactionStatus;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsTable extends Component
{
    use WithPagination;

    public int $limit = 15;
    public bool $paginated = false;
    public bool $showSearch = false;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $currencyId = null;

    #[Url]
    public string $status = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $performer = 'user';

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    public function updating()
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'currencyId', 'status', 'type', 'performer', 'startDate', 'endDate']);
    }

    #[Computed]
    public function transactions()
    {
        $query = Transaction::query()
            ->with(['user', 'currency'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('amount', 'like', '%' . $this->search . '%')
                        ->orWhere('reference', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->currencyId, fn($q) => $q->where('currency_id', $this->currencyId))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->type, fn($q) => $q->where('action', $this->type))
            ->when($this->performer, function($q) {
                if ($this->performer === 'user') {
                    return $q->where('user_id', '>', 0);
                }
                if ($this->performer === 'system') {
                    return $q->where('user_id', 0);
                }
            })
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->latest();

        if ($this->paginated) {
            return $query->paginate($this->limit);
        }

        return $query->take($this->limit)->get();
    }

    #[Computed]
    public function currencies()
    {
        return Currency::all();
    }

    public function render()
    {
        return view('livewire.admin.components.transactions-table');
    }
}
