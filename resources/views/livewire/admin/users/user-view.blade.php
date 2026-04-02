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
                                            {{ number_format($wallet->balance, 8) }} <span class="text-sm font-normal text-slate-400">{{ $wallet->currency->symbol }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-2 py-1 rounded bg-success/10 text-success text-[10px] font-bold uppercase">{{ $wallet->status->value }}</div>
                            </div>

                            <div class="mt-auto pt-4 overflow-hidden">
                                <div class="text-[10px] text-slate-400 uppercase font-bold mb-1">Wallet Address</div>
                                <div class="flex items-center bg-slate-50 dark:bg-darkmode-400 p-2 rounded border border-dashed border-slate-300/50 dark:border-darkmode-400">
                                    <code class="text-[11px] text-slate-600 dark:text-slate-400 break-all flex-1 truncate mr-2" id="wallet-{{ $wallet->id }}">
                                        {{ $wallet->address ?: 'Processing...' }}
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
                    <input wire:model.live="startDate" type="date" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary" title="Start Date">
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <input wire:model.live="endDate" type="date" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary" title="End Date">
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
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Description</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Asset</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right">Amount</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right">USD</th>
                            <th class="px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-darkmode-400">
                        @forelse($this->transactions as $tx)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-darkmode-400/30 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-400 font-mono">
                                    {{ $tx->created_at->format('M d, Y') }}
                                    <div class="text-[10px] opacity-60">{{ $tx->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $tx->description ?: $tx->action->label() }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono uppercase">{{ $tx->reference }}</div>
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
                                        {{ $tx->type->value == 'credit' ? '+' : '-' }} {{ number_format($tx->amount, 8) }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-sm text-slate-500">
                                    ${{ number_format($tx->usd, 2) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ strtolower($tx->status?->value ?? '') == 'completed' ? 'bg-success/10 text-success' : (in_array(strtolower($tx->status?->value ?? ''), ['pending', 'processing']) ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger') }}">
                                        {{ $tx->status?->label() ?? 'Pending' }}
                                    </span>
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
</div>
