<?php

namespace App\Livewire\Admin\Currencies;

use App\Domains\Wallet\Models\Currency;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CurrenciesManagement extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = ''; // crypto, fiat

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'type']);
    }

    #[Computed]
    public function currencies()
    {
        return Currency::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('symbol', 'like', '%' . $this->search . '%');
            })
            ->when($this->type === 'crypto', fn($q) => $q->where('is_crypto', true))
            ->when($this->type === 'fiat', fn($q) => $q->where('is_crypto', false))
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.currencies.currencies-management')->layout('layouts.app');
    }
}
