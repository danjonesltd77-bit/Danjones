<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12 flex flex-col items-center gap-y-3 sm:flex-row sm:h-10">
        <div class="flex items-center">
            <a wire:navigate href="{{ route('admin.currencies.index') }}" class="mr-3 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-darkmode-400 flex items-center justify-center mr-3 font-bold text-xs uppercase">
                    @if($currency->image)
                        <img src="{{ $currency->image }}" class="w-5 h-5 object-contain">
                    @else
                        {{ $currency->symbol[0] }}
                    @endif
                </div>
                <div class="text-base font-medium group-[.mode--light]:text-white uppercase tracking-wider">
                    {{ $currency->name }} Audit Log
                </div>
            </div>
        </div>
    </div>

    <!-- System Balance Overview -->
    <div class="col-span-12 grid grid-cols-12 gap-5">
        <div class="col-span-12 flex items-center justify-between">
            <div class="text-xs font-bold uppercase tracking-widest text-slate-500">Internal System Wallets</div>
            @if($currency->is_crypto)
                <button wire:click="refreshAllBlockchainBalances" wire:loading.attr="disabled" class="text-[10px] font-bold uppercase tracking-widest text-primary flex items-center hover:opacity-80 transition-opacity">
                    <i data-lucide="refresh-cw" class="w-3 h-3 mr-2" wire:loading.class="animate-spin" wire:target="refreshAllBlockchainBalances"></i>
                    Refresh All On-chain Balances
                </button>
            @endif
        </div>

        @forelse($this->systemWallets as $wallet)
            <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-6 xl:col-span-4">
                <div class="flex items-center">
                    <div class="h-[54px] w-[54px] flex items-center justify-center rounded-full border border-primary/20 bg-slate-50 dark:bg-darkmode-400 text-primary shadow-sm">
                        <i data-lucide="wallet" class="w-6 h-6"></i>
                    </div>
                    <div class="ml-4">
                        <div class="-mt-0.5 text-lg font-medium text-slate-700 dark:text-slate-300">
                            {{ $wallet->type->label() }} Wallet
                        </div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $currency->symbol }} System Fund</div>
                    </div>
                </div>
                <div class="box mt-8 rounded-[0.6rem] border border-dashed border-slate-300/80 dark:border-darkmode-400 px-4 py-3 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-medium leading-tight text-slate-800 dark:text-slate-300">
                                {{ crypto_format($wallet->balance, $currency->decimal) }}
                            </div>
                            <div class="mt-1 text-[10px] text-slate-400 uppercase tracking-widest font-bold">Ledger</div>
                        </div>

                        @if($wallet->type === \App\Enum\SystemWalletType::GAS || isset($this->blockchainBalances[$wallet->id]))
                            <div class="text-right border-l border-slate-100 dark:border-darkmode-400 pl-4">
                                <div class="flex items-center justify-end text-primary">
                                    <div class="text-sm font-bold">
                                        @if(isset($this->blockchainBalances[$wallet->id]))
                                            {{ crypto_format($this->blockchainBalances[$wallet->id], $wallet->type === \App\Enum\SystemWalletType::GAS && $currency->parent_id ? $currency->parent->decimal : $currency->decimal) }}
                                            <span class="text-[8px] opacity-70 ml-0.5">{{ $wallet->type === \App\Enum\SystemWalletType::GAS && $currency->parent_id ? $currency->parent->symbol : $currency->symbol }}</span>
                                        @else
                                            <span class="text-slate-300">--</span>
                                        @endif
                                    </div>
                                    <button wire:click="refreshBlockchainBalance({{ $wallet->id }})" wire:loading.attr="disabled" class="ml-2 p-1 rounded-md hover:bg-slate-100 dark:hover:bg-darkmode-400 transition-colors">
                                        <i data-lucide="refresh-cw" class="w-3 h-3" wire:loading.class="animate-spin" wire:target="refreshBlockchainBalance({{ $wallet->id }})"></i>
                                    </button>
                                </div>
                                <div class="mt-1 text-[10px] text-slate-400 uppercase tracking-widest font-bold">On-chain</div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4 flex items-center text-[10px] text-slate-400">
                    <code class="px-2 py-0.5 bg-slate-50 dark:bg-darkmode-400 rounded break-all">
                        {{ $wallet->address ?: 'Internal Protocol Wallet' }}
                    </code>
                    @if($wallet->address && $currency->is_crypto)
                        <button wire:click="initiateSend({{ $wallet->id }})" class="ml-auto inline-flex items-center text-primary hover:text-primary/80 transition-colors">
                            <i data-lucide="send" class="w-3.5 h-3.5 mr-1"></i>
                            Send
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-12 box box--stacked p-10 flex flex-col items-center justify-center text-slate-400">
                <i data-lucide="info" class="w-10 h-10 mb-4 opacity-20"></i>
                <div class="text-lg font-medium">No internal system wallets found for {{ $currency->name }}</div>
                <div class="text-xs">This asset uses standard user-to-user routing for all platform movements.</div>
            </div>
        @endforelse
    </div>

    <!-- Transaction Ledger -->
    <div class="col-span-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-4">
            <div class="flex items-center">
                <div class="text-base font-medium">
                    {{ $currency->id === 1 ? 'Global User Movements' : 'System Movement Audit' }}
                </div>
                <div class="ml-3 px-2 py-0.5 bg-slate-100 dark:bg-darkmode-400 rounded text-[10px] text-slate-400 uppercase tracking-widest font-bold">Real-time Ledger</div>
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-[200px]">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search reference..." class="w-full pl-9 h-9 bg-box dark:bg-darkmode-400 border-none rounded-lg text-xs focus:ring-1 focus:ring-primary shadow-sm">
                </div>

                @if($currency->id !== 1 && $this->systemWallets->count() > 0)
                    <select wire:model.live="systemWalletId" class="h-9 bg-box dark:bg-darkmode-400 border-none rounded-lg text-xs focus:ring-1 focus:ring-primary shadow-sm min-w-[150px]">
                        <option value="">All System Wallets</option>
                        @foreach($this->systemWallets as $wallet)
                            <option value="{{ $wallet->id }}">{{ $wallet->type->label() }} Wallet</option>
                        @endforeach
                    </select>
                @endif

                <select wire:model.live="type" class="h-9 bg-box dark:bg-darkmode-400 border-none rounded-lg text-xs focus:ring-1 focus:ring-primary shadow-sm min-w-[100px]">
                    <option value="">All Types</option>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
                <select wire:model.live="action" class="h-9 bg-box dark:bg-darkmode-400 border-none rounded-lg text-xs focus:ring-1 focus:ring-primary shadow-sm min-w-[100px]">
                    <option value="">All Actions</option>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                    <option value="transfer">Transfer</option>
                    <option value="fee">Fee</option>
                    <option value="refund">Refund</option>
                    <option value="sell">Sell</option>
                    <option value="buy">Buy</option>
                </select>
                <button wire:click="resetFilters" class="h-9 px-3 text-slate-500 hover:text-primary hover:bg-slate-50 dark:hover:bg-darkmode-400 rounded-lg transition-colors shadow-sm bg-box dark:bg-darkmode-600">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div class="box box--stacked p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-darkmode-400/50">
                            <th wire:click="sortBy('created_at')" class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 cursor-pointer hover:text-primary transition-colors group">
                                <div class="flex items-center">
                                    Timestamp
                                    @if($sortField === 'created_at')
                                        <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3 ml-1.5 text-primary"></i>
                                    @else
                                        <i data-lucide="chevrons-up-down" class="w-3 h-3 ml-1.5 opacity-0 group-hover:opacity-40"></i>
                                    @endif
                                </div>
                            </th>
                            <th class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Type/Action</th>
                            <th class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Source/Actor</th>
                            <th class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Reference</th>
                            <th wire:click="sortBy('amount')" class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right cursor-pointer hover:text-primary transition-colors group">
                                <div class="flex items-center justify-end">
                                    Amount
                                    @if($sortField === 'amount')
                                        <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3 ml-1.5 text-primary"></i>
                                    @else
                                        <i data-lucide="chevrons-up-down" class="w-3 h-3 ml-1.5 opacity-0 group-hover:opacity-40"></i>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('current_balance')" class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right cursor-pointer hover:text-primary transition-colors group">
                                <div class="flex items-center justify-end">
                                    Running Balance
                                    @if($sortField === 'current_balance')
                                        <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3 ml-1.5 text-primary"></i>
                                    @else
                                        <i data-lucide="chevrons-up-down" class="w-3 h-3 ml-1.5 opacity-0 group-hover:opacity-40"></i>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-darkmode-400">
                        @forelse($this->transactions as $tx)
                            <tr class="hover:bg-slate-50 dark:hover:bg-darkmode-400/20 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $tx->created_at->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $tx->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase mr-2 {{ $tx->type->color() }}">
                                            {{ $tx->type->label() }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">{{ $tx->action->label() }}</span>
                                    </div>
                                    <div class="text-[10px] mt-1 text-slate-400 italic max-w-xs truncate">{{ $tx->description }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($tx->user)
                                        <a href="{{ route('admin.users.show', $tx->user->id) }}" wire:navigate class="group flex items-center">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-darkmode-400 flex items-center justify-center text-[10px] font-bold text-primary mr-3 group-hover:bg-primary group-hover:text-white transition-all shadow-sm border border-slate-200 dark:border-darkmode-400">
                                                {{ strtoupper(substr($tx->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors underline decoration-dotted decoration-slate-300 underline-offset-4">{{ $tx->user->name }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $tx->user->email }}</div>
                                            </div>
                                        </a>
                                    @else
                                        <div class="flex items-center">
                                            <div class="w-7 h-7 rounded-full bg-slate-50 dark:bg-darkmode-600 flex items-center justify-center text-slate-400 mr-3 border border-dashed border-slate-300 dark:border-darkmode-400">
                                                <i data-lucide="shield" class="w-3 h-3"></i>
                                            </div>
                                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">System Internal</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <code class="text-[10px] text-slate-400 select-all">{{ $tx->reference }}</code>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="text-sm font-bold {{ $tx->type->isCredit() ? 'text-success' : 'text-danger' }}">
                                        {{ $tx->type->isCredit() ? '+' : '-' }}{{ crypto_format($tx->amount, $currency->decimal) }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="text-xs font-medium text-slate-500">{{ crypto_format($tx->current_balance, $currency->decimal) }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $currency->id === 1 ? 6 : 5 }}" class="px-5 py-20 text-center text-slate-400">
                                    <i data-lucide="database" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                                    <div class="text-sm font-medium">No ledger records found for this asset.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($this->transactions->hasPages())
                <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400 bg-slate-50/50 dark:bg-darkmode-400/50">
                    {{ $this->transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    @if($currency->is_crypto)
        <div 
            x-data="{ show: false }"
            @open-modal.window="if ($event.detail.id === 'admin-send-modal') { show = true }"
            @close-modal.window="if ($event.detail.id === 'admin-send-modal') { show = false }"
            x-show="show"
            class="fixed inset-0 z-[60] overflow-y-auto"
            style="display: none;"
        >
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" @click="show = false"></div>

                <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-darkmode-600 sm:my-40 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="flex items-center justify-between mb-5 font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                        <h3 class="text-sm">Initiate Admin Transfer</h3>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-500">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form wire:submit="processSend">
                        @if($this->selectedSendingWallet)
                            <div class="mb-5 p-4 rounded-lg bg-slate-50 dark:bg-darkmode-400 border border-slate-200 dark:border-darkmode-500/50">
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Source Internal Wallet</div>
                                <div class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $this->selectedSendingWallet->type->label() }} Wallet</div>
                                <div class="mt-2 text-xs flex items-center justify-between text-slate-500">
                                    <span>Available Balance:</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ crypto_format($this->selectedSendingWallet->balance, $currency->decimal) }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Recipient Public Address</label>
                                <input wire:model="recipientAddress" type="text" placeholder="Enter the destination wallet address" class="w-full h-10 px-4 bg-slate-50 dark:bg-darkmode-800 border-none rounded-lg text-xs font-mono focus:ring-1 focus:ring-primary shadow-sm" required>
                                @error('recipientAddress') <span class="text-danger text-[10px] mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Amount to Send ({{ $currency->symbol }})</label>
                                <input wire:model="amount" type="number" step="0.000001" placeholder="0.000000" class="w-full h-10 px-4 bg-slate-50 dark:bg-darkmode-800 border-none rounded-lg text-xs focus:ring-1 focus:ring-primary shadow-sm" required>
                                @error('amount') <span class="text-danger text-[10px] mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex items-center justify-end gap-3">
                            <button type="button" @click="show = false" class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:bg-slate-50 dark:hover:bg-darkmode-400 transition-colors">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-[10px] font-bold uppercase tracking-widest rounded-lg bg-primary text-white hover:bg-primary/90 transition-colors shadow-sm disabled:opacity-70 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                                <span wire:loading.remove>Confirm Transfer</span>
                                <span wire:loading>Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
