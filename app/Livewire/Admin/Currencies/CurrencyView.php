<?php

namespace App\Livewire\Admin\Currencies;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class CurrencyView extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = ''; // debit, credit

    #[Url]
    public string $action = ''; // deposit, fee, etc.

    #[Url]
    public ?int $systemWalletId = null;

    #[Url]
    public string $sortField = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    public Currency $currency;

    /**
     * Mount the component with a specific currency.
     */
    public function mount(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Handle filter resets when searching.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Reset all audit filters.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'type', 'action', 'systemWalletId']);
    }

    /**
     * Toggle sorting direction or change field.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Get all system wallets for the current currency.
     */
    #[Computed]
    public function systemWallets()
    {
        return SystemWallet::where('currency_id', $this->currency->id)->get();
    }

    /**
     * Get the transaction ledger for this currency.
     */
    #[Computed]
    public function transactions()
    {
        $query = Transaction::where('currency_id', $this->currency->id);

        // Contextual focus (Naira vs Crypto)
        if ($this->currency->id !== 1) {
            $query->where('wallet_type', SystemWallet::class);
        }

        // Apply Filters
        $query->when($this->search, function ($q) {
            $q->where(function ($sub) {
                $sub->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->type, fn($q) => $q->where('type', $this->type))
        ->when($this->action, fn($q) => $q->where('action', $this->action))
        ->when($this->systemWalletId, fn($q) => $q->where('wallet_id', $this->systemWalletId));

        return $query->with('user')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.currencies.currency-view')->layout('layouts.app');
    }
}
