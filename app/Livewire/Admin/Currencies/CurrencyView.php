<?php

namespace App\Livewire\Admin\Currencies;

use App\Domains\Wallet\Actions\AdminSendAction;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Transaction;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
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

    // Send state
    public ?int $sendingWalletId = null;

    public string $recipientAddress = '';

    public string $amount = '';

    // Blockchain balance state
    public array $blockchainBalances = [];

    /**
     * Mount the component with a specific currency.
     */
    public function mount(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Refresh the blockchain balance for a specific system wallet.
     */
    /**
     * Refresh the blockchain balance for a specific system wallet.
     */
    public function refreshBlockchainBalance(
        int $walletId,
        \App\Domains\Wallet\Contracts\CryptoGatewayInterface $gateway,
        \App\Domains\Wallet\Contracts\TransactionRepositoryInterface $repository,
        \App\Domains\Wallet\Contracts\MarketDataGatewayInterface $marketData
    ): void {
        Gate::authorize('super-admin');

        try {
            $wallet = SystemWallet::findOrFail($walletId);

            // For gas wallets, we always check the native asset balance
            $checkCurrency = $this->currency;
            if ($wallet->type === \App\Enum\SystemWalletType::GAS && $this->currency->parent_id) {
                $checkCurrency = $this->currency->parent;
            }

            $blockchainBalance = $gateway->getBalance($wallet->address, $checkCurrency);
            $this->blockchainBalances[$walletId] = $blockchainBalance;

            // Update ledger if there is a discrepancy
            $ledgerBalance = (float) $wallet->balance;
            $diff = $blockchainBalance - $ledgerBalance;

            // if (abs($diff) > 0.00000001) {
            //     $rate = $marketData->getExchangeRate($wallet->currency_id);
            //     $type = $diff > 0 ? 'credit' : 'debit';
            //     $amount = abs($diff);

            //     $repository->recordEntry(
            //         $wallet,
            //         $amount,
            //         $amount * $rate,
            //         $type,
            //         'transfer',
            //         'SYNC-'.strtoupper(\Illuminate\Support\Str::random(10)),
            //         "Blockchain sync: Adjusted balance from {$ledgerBalance} to {$blockchainBalance}",
            //         ['blockchain_balance' => $blockchainBalance, 'previous_ledger' => $ledgerBalance],
            //         'completed'
            //     );
            // }

            $this->dispatch('toast', message: "Blockchain balance synchronized for {$wallet->type->label()}.", variant: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Failed to synchronize blockchain balance: '.$e->getMessage(), variant: 'error');
        }
    }

    /**
     * Refresh all blockchain balances for gas wallets.
     */
    public function refreshAllBlockchainBalances(
        \App\Domains\Wallet\Contracts\CryptoGatewayInterface $gateway,
        \App\Domains\Wallet\Contracts\TransactionRepositoryInterface $repository,
        \App\Domains\Wallet\Contracts\MarketDataGatewayInterface $marketData
    ): void {
        Gate::authorize('super-admin');

        foreach ($this->systemWallets as $wallet) {
            if ($wallet->type === \App\Enum\SystemWalletType::GAS || $this->currency->is_gaspump) {
                $this->refreshBlockchainBalance($wallet->id, $gateway, $repository, $marketData);
            }
        }
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
     * Open the send modal for a specific system wallet.
     */
    public function initiateSend(int $walletId): void
    {
        Gate::authorize('super-admin');

        $this->sendingWalletId = $walletId;
        $this->reset(['recipientAddress', 'amount']);
        $this->dispatch('open-modal', id: 'admin-send-modal');
    }

    /**
     * Process the administrative crypto send.
     */
    public function processSend(AdminSendAction $action): void
    {
        Gate::authorize('super-admin');

        $this->validate([
            'recipientAddress' => 'required|string|min:10',
            'amount' => 'required|numeric|gt:0',
        ]);

        try {
            $wallet = SystemWallet::findOrFail($this->sendingWalletId);

            $action->execute($wallet, (float) $this->amount, $this->recipientAddress);

            $this->dispatch('close-modal', id: 'admin-send-modal');
            $this->dispatch('toast', message: 'Crypto sent successfully!', variant: 'success');

            $this->reset(['sendingWalletId', 'recipientAddress', 'amount']);
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), variant: 'error');
        }
    }

    /**
     * Get the currently selected sending wallet.
     */
    #[Computed]
    public function selectedSendingWallet()
    {
        return $this->sendingWalletId ? SystemWallet::find($this->sendingWalletId) : null;
    }

    /**
     * Get all system wallets for the current currency.
     */
    #[Computed]
    public function systemWallets()
    {
        $query = SystemWallet::where('currency_id', $this->currency->id);

        if ($this->currency->parent_id) {
            $query->orWhere(function ($q) {
                $q->where('currency_id', $this->currency->parent_id)
                    ->where('type', \App\Enum\SystemWalletType::GAS);
            });
        }

        return $query->get();
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
                $sub->where('reference', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        })
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->action, fn ($q) => $q->where('action', $this->action))
            ->when($this->systemWalletId, fn ($q) => $q->where('wallet_id', $this->systemWalletId));

        return $query->with('user')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.currencies.currency-view')->layout('layouts.app');
    }
}
