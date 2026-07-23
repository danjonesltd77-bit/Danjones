<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <!-- Header -->
    <div class="col-span-12">
        <div class="flex flex-col items-center gap-y-3 sm:flex-row sm:h-10">
            <div class="flex items-center">
                <a wire:navigate href="{{ route('admin.users.index') }}" class="mr-4 text-slate-500 hover:text-primary transition-colors">
                    <i data-lucide="arrow-left" class="w-6 h-6"></i>
                </a>
                <div class="text-base font-medium group-[.mode--light]:text-white">User Details: {{ $user->name }}</div>
            </div>
            <div class="flex items-center gap-x-3 sm:ml-auto">
                <div class="px-3 py-1 text-xs font-medium rounded-full {{ $user->email_verified_at ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                    {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                </div>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-12 gap-5">
            <!-- User Status Card -->
            <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-12 lg:col-span-4">
                <div class="flex items-center">
                    <div class="h-[64px] w-[64px] flex items-center justify-center rounded-full border border-primary/20 bg-slate-50 dark:bg-darkmode-400 text-primary text-xl font-bold">
                        {{ $user->initials() }}
                    </div>
                    <div class="ml-4">
                        <div class="text-lg font-medium text-slate-700 dark:text-slate-300">{{ $user->name }}</div>
                        <div class="text-slate-500 text-sm">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="mt-6 border-t border-dashed border-slate-300/80 dark:border-darkmode-400 pt-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-slate-500 text-sm">Member Since</span>
                        <span class="text-slate-700 dark:text-slate-300 font-medium font-mono text-sm">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-sm">Role</span>
                        <div class="flex flex-wrap gap-1 justify-end">
                            @foreach($user->roles as $role)
                                <span class="px-2 py-0.5 bg-slate-100 dark:bg-darkmode-400 rounded text-[10px] uppercase font-bold text-slate-600 dark:text-slate-400">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallets Grid -->
            <div class="col-span-12 lg:col-span-8">
                <div class="grid grid-cols-12 gap-5">
                    @foreach($user->wallets as $wallet)
                        <div class="box box--stacked col-span-12 sm:col-span-6 p-5 flex flex-col justify-between min-h-[160px]">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-lg bg-primary/10 text-primary mr-3">
                                        <i data-lucide="wallet" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-slate-500 uppercase tracking-wider">{{ $wallet->currency->name }}</div>
                                        <div class="text-2xl font-bold text-slate-800 dark:text-slate-200 mt-1">
                                            {{ crypto_format($wallet->balance, $wallet->currency->decimal ?? 8) }} 
                                            <span class="text-sm font-normal text-slate-400">{{ $wallet->currency->symbol }}</span>
                                        </div>
                                        <div class="text-sm font-medium text-slate-500 mt-1">
                                            ≈ ${{ crypto_format($wallet->balance * ($this->currencyRates[$wallet->currency_id] ?? 0), 2) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="px-2 py-1 rounded bg-success/10 text-success text-[10px] font-bold uppercase">{{ $wallet->status->value }}</div>
                            </div>

                            <div class="mt-auto pt-4 overflow-hidden">
                                <div class="text-[10px] text-slate-400 uppercase font-bold mb-1">Wallet Address</div>
                                <div class="flex items-center bg-slate-50 dark:bg-darkmode-400 p-2 rounded border border-dashed border-slate-300/50 dark:border-darkmode-400">
                                    <code class="text-[11px] font-mono text-slate-600 dark:text-slate-400 flex-1 truncate mr-2" id="wallet-{{ $wallet->id }}" title="{{ $wallet->address }}">
                                        @if($wallet->address)
                                            @if(strlen($wallet->address) > 16)
                                                {{ substr($wallet->address, 0, 8) }}...{{ substr($wallet->address, -8) }}
                                            @else
                                                {{ $wallet->address }}
                                            @endif
                                        @else
                                            Processing...
                                        @endif
                                    </code>
                                    @if($wallet->address)
                                        <button 
                                            onclick="navigator.clipboard.writeText('{{ $wallet->address }}'); alert('Address copied!');"
                                            class="text-slate-400 hover:text-primary transition-colors flex-shrink-0"
                                            title="Copy Address"
                                        >
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @if($wallet->currency->symbol === 'NGN')
                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-darkmode-400/50 flex justify-end">
                                    <button 
                                        @click="$dispatch('open-modal', { id: 'credit-wallet-modal' })"
                                        class="inline-flex items-center gap-x-1.5 px-3 py-1.5 bg-primary text-white text-[11px] font-bold uppercase tracking-wider rounded-lg hover:bg-primary/90 transition-all shadow-sm"
                                    >
                                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                        Manual Credit
                                    </button>
                                </div>
                            @elseif($wallet->currency->is_gaspump)
                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-darkmode-400/50 flex items-center justify-between gap-2">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider flex items-center">
                                        <i data-lucide="zap" class="w-3 h-3 text-amber-500 mr-1"></i> Gaspump (Idx: {{ $wallet->index }})
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <button 
                                            wire:click="checkGaspumpStatus({{ $wallet->id }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-x-1 px-2.5 py-1 bg-slate-100 dark:bg-darkmode-400 text-slate-600 dark:text-slate-300 text-[10px] font-bold uppercase tracking-wider rounded hover:bg-slate-200 dark:hover:bg-darkmode-300 transition-colors"
                                            title="Check activation status on Tatum">
                                            <i data-lucide="shield-check" class="w-3 h-3" wire:loading.class="animate-spin" wire:target="checkGaspumpStatus({{ $wallet->id }})"></i>
                                            Check Tatum Status
                                        </button>
                                        @if($wallet->status->value !== 'active')
                                            <button 
                                                wire:click="activateGaspumpWallet({{ $wallet->id }})"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center gap-x-1 px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider rounded hover:bg-primary/20 transition-colors"
                                                title="Send activation transaction to Tatum">
                                                <i data-lucide="zap" class="w-3 h-3" wire:loading.class="animate-spin" wire:target="activateGaspumpWallet({{ $wallet->id }})"></i>
                                                Activate
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History Section -->
    <div class="col-span-12 flex flex-col gap-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-y-3">
            <div class="text-base font-medium">Transaction History</div>
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="resetFilters" class="text-xs text-slate-500 hover:text-primary underline">Clear Filters</button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="box box--stacked p-5">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-4">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search reference or amount..." class="w-full pl-10 h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="currencyId" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Currencies</option>
                        @foreach($this->currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->symbol }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="status" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Status</option>
                        @foreach(\App\Enum\TransactionStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="type" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Actions</option>
                        @foreach(\App\Enum\TransactionAction::cases() as $action)
                            <option value="{{ $action->value }}">{{ $action->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-1">
                    <input wire:model.live="startDate" type="date" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-[10px] focus:ring-1 focus:ring-primary" title="Start Date">
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-1">
                    <input wire:model.live="endDate" type="date" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-[10px] focus:ring-1 focus:ring-primary" title="End Date">
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="box box--stacked p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-darkmode-400/50">
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Date</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Action Type</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Description</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Asset</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right">Amount</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-center">Status</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-darkmode-400">
                        @forelse($this->transactions as $tx)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-darkmode-400/30 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-400 font-mono">
                                    {{ $tx->created_at->format('M d, Y') }}
                                    <div class="text-[10px] opacity-60">{{ $tx->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300 capitalize">
                                        {{ str_replace('_', ' ', $tx->action?->value ?? 'unknown') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono tracking-tight uppercase">{{ $tx->reference }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-xs text-slate-500 max-w-[150px] truncate">{{ $tx->description ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-darkmode-400 text-[10px] font-bold text-slate-500">
                                            {{ $tx->currency->symbol }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-bold {{ $tx->type->value == 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $tx->type->value == 'credit' ? '+' : '-' }} {{ crypto_format($tx->amount, $tx->currency?->decimal ?? 8) }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">${{ crypto_format($tx->usd, 2) }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ strtolower($tx->status?->value ?? '') == 'completed' ? 'bg-success/10 text-success' : (in_array(strtolower($tx->status?->value ?? ''), ['pending', 'processing']) ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger') }}">
                                        {{ $tx->status?->label() ?? 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <a wire:navigate href="{{ route('admin.transactions.show', $tx) }}" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-darkmode-400 text-primary transition-colors flex items-center justify-center mx-auto" title="View Transaction">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400 italic">
                                    <div class="flex flex-col items-center justify-center opacity-50">
                                        <i data-lucide="inbox" class="w-12 h-12 mb-3"></i>
                                        <p>No transactions found for this user.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($this->transactions->hasPages())
                <div class="px-5 py-4 border-t border-slate-200/60 dark:border-darkmode-400">
                    {{ $this->transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Credit Wallet Modal -->
    <div 
        x-data="{ show: false }"
        @open-modal.window="if ($event.detail.id === 'credit-wallet-modal') { show = true }"
        @close-modal.window="if ($event.detail.id === 'credit-wallet-modal') { show = false }"
        x-show="show"
        class="fixed inset-0 z-[60] overflow-y-auto"
        style="display: none;"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" @click="show = false"></div>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-darkmode-600 sm:my-40 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="flex items-center justify-between mb-5 font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                    <h3 class="text-sm flex items-center gap-x-2">
                        <i data-lucide="plus-circle" class="w-5 h-5 text-primary"></i>
                        Manual Naira Wallet Credit
                    </h3>
                    <button @click="show = false" class="text-slate-400 hover:text-slate-500">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit="processCredit">
                    <div class="mb-5 p-4 rounded-lg bg-slate-50 dark:bg-darkmode-400 border border-slate-200 dark:border-darkmode-500/50">
                        <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Target User</div>
                        <div class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $user->name }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ $user->email }}</div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Amount to Credit (NGN)</label>
                            <input wire:model="creditAmount" type="number" step="0.01" placeholder="0.00" class="w-full h-10 px-4 bg-slate-50 dark:bg-darkmode-800 border-none rounded-lg text-xs focus:ring-1 focus:ring-primary shadow-sm" required>
                            @error('creditAmount') <span class="text-danger text-[10px] mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Custom Reference (Optional)</label>
                            <input wire:model="creditReference" type="text" placeholder="Enter custom ref or leave empty to auto-generate" class="w-full h-10 px-4 bg-slate-50 dark:bg-darkmode-800 border-none rounded-lg text-xs font-mono focus:ring-1 focus:ring-primary shadow-sm">
                            @error('creditReference') <span class="text-danger text-[10px] mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Description / Narration</label>
                            <textarea wire:model="creditDescription" rows="3" placeholder="Explain the reason for this manual adjustment..." class="w-full p-4 bg-slate-50 dark:bg-darkmode-800 border-none rounded-lg text-xs focus:ring-1 focus:ring-primary shadow-sm" required></textarea>
                            @error('creditDescription') <span class="text-danger text-[10px] mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Administrator Transaction PIN</label>
                            <input wire:model="adminPin" type="password" maxlength="4" placeholder="••••" class="w-full h-10 px-4 bg-slate-50 dark:bg-darkmode-800 border-none rounded-lg text-xs text-center tracking-widest font-mono focus:ring-1 focus:ring-primary shadow-sm" required>
                            @error('adminPin') <span class="text-danger text-[10px] mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end gap-3">
                        <button type="button" @click="show = false" class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:bg-slate-50 dark:hover:bg-darkmode-400 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-[10px] font-bold uppercase tracking-widest rounded-lg bg-primary text-white hover:bg-primary/90 transition-colors shadow-sm disabled:opacity-70 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                            <span wire:loading.remove>Confirm Credit</span>
                            <span wire:loading>Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
