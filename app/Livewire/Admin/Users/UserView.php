<?php

namespace App\Livewire\Admin\Users;

use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Services\LedgerService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    // Wallet credit properties
    public string $creditAmount = '';

    public string $creditReference = '';

    public string $creditDescription = '';

    public string $adminPin = '';

    public function mount(User $user)
    {
        $this->user = $user->load([
            'wallets.currency',
            'bankAccounts',
            'p2pAdvertisements',
            'roles',
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

    public function processCredit(
        LedgerService $ledgerService,
        MarketDataGatewayInterface $marketDataGateway
    ): void {
        Gate::authorize('manage wallets');

        $this->validate([
            'creditAmount' => 'required|numeric|gt:0',
            'creditDescription' => 'required|string|min:5',
            'adminPin' => 'required|string|size:4',
        ], [
            'creditAmount.required' => 'Please enter an amount.',
            'creditAmount.numeric' => 'The amount must be a number.',
            'creditAmount.gt' => 'The amount must be greater than zero.',
            'creditDescription.required' => 'Please describe the reason for this credit.',
            'creditDescription.min' => 'The reason must be at least 5 characters.',
            'adminPin.required' => 'Administrator PIN is required.',
            'adminPin.size' => 'The PIN must be a 4-digit code.',
        ]);

        $admin = auth()->user();

        if (empty($admin->pin)) {
            throw ValidationException::withMessages([
                'adminPin' => ['You must set a transaction PIN before performing this action.'],
            ]);
        }

        if (! Hash::check($this->adminPin, $admin->pin)) {
            throw ValidationException::withMessages([
                'adminPin' => ['The administrator PIN is incorrect.'],
            ]);
        }

        try {
            $currency = Currency::where('symbol', 'NGN')->firstOrFail();

            $wallet = $this->user->wallets()->where('currency_id', $currency->id)->first();
            if (! $wallet) {
                $wallet = $this->user->wallets()->create([
                    'name' => $this->user->name,
                    'currency_id' => $currency->id,
                    'address' => $this->user->email,
                    'status' => 'active',
                ]);
            }

            DB::transaction(function () use ($ledgerService, $marketDataGateway, $wallet) {
                $usdNgnRate = $marketDataGateway->getUsdNgnRate();
                $usdAmount = (float) $this->creditAmount / ($usdNgnRate ?: 1);

                $ref = $this->creditReference ?: 'MAN-NGN-CRED-'.strtoupper(Str::random(10));

                $ledgerService->recordDeposit(
                    null,
                    $wallet,
                    (float) $this->creditAmount,
                    $usdAmount,
                    $ref,
                    $this->creditDescription,
                    ['admin_id' => auth()->id()],
                    'completed'
                );
            });

            $this->user->load([
                'wallets.currency',
                'bankAccounts',
                'p2pAdvertisements',
                'roles',
            ]);

            $this->reset(['creditAmount', 'creditReference', 'creditDescription', 'adminPin']);
            $this->dispatch('close-modal', id: 'credit-wallet-modal');
            $this->dispatch('toast', message: 'Naira wallet credited successfully!', variant: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Failed to credit Naira wallet: '.$e->getMessage(), variant: 'error');
        }
    }

    #[Computed]
    public function transactions()
    {
        return Transaction::query()
            ->with(['currency'])
            ->where('user_id', $this->user->id)
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('description', 'like', '%'.$this->search.'%')
                        ->orWhere('amount', 'like', '%'.$this->search.'%')
                        ->orWhere('reference', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->currencyId, fn ($q) => $q->where('currency_id', $this->currencyId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->type, fn ($q) => $q->where('action', $this->type))
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate))
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
