<?php

namespace App\Livewire\Admin\Users;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Transaction;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserView extends Component
{
    use WithPagination;

    public User $user;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $currencyId = null;

    #[Url]
    public string $status = '';

    #[Url]
    public string $type = '';

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    public function mount(User $user)
    {
        $this->user = $user->load([
            'wallets.currency',
            'bankAccounts',
            'p2pAdvertisements',
            'roles'
        ]);
    }

    public function updating()
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'currencyId', 'status', 'type', 'startDate', 'endDate']);
    }

    #[Computed]
    public function transactions()
    {
        return Transaction::query()
            ->with(['currency'])
            ->where('user_id', $this->user->id)
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('description', 'like', '%' . $this->search . '%')
                        ->orWhere('amount', 'like', '%' . $this->search . '%')
                        ->orWhere('reference', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->currencyId, fn($q) => $q->where('currency_id', $this->currencyId))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->type, fn($q) => $q->where('action', $this->type))
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function currencies()
    {
        return Currency::all();
    }

    #[Computed]
    public function currencyRates(): array
    {
        $marketData = app(\App\Domains\Wallet\Contracts\MarketDataGatewayInterface::class);
        $currencyIds = $this->user->wallets->pluck('currency_id')->unique();

        return $currencyIds->mapWithKeys(function ($id) use ($marketData) {
            $currency = Currency::find($id);

            // Special handling for NGN if needed, else use gateway
            if ($currency->symbol === 'NGN') {
                return [$id => 1 / $marketData->getUsdNgnRate()];
            }

            return [$id => $marketData->getExchangeRate($id)];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.admin.users.user-view')->layout('layouts.app');
    }
}
