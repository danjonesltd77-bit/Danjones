<div>
    @if($showSearch)
        <div class="p-5 border-b border-slate-200/60 dark:border-darkmode-400">
            <div class="grid grid-cols-12 gap-4">
                <!-- Search -->
                <div class="col-span-12 md:col-span-4">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search reference or user..." class="w-full pl-10 h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                    </div>
                </div>
                
                <!-- Currency -->
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="currencyId" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Assets</option>
                        @foreach($this->currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->symbol }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="status" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Status</option>
                        @foreach(\App\Enum\TransactionStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type (Action) -->
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="type" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Actions</option>
                        @foreach(\App\Enum\TransactionAction::cases() as $action)
                            <option value="{{ $action->value }}">{{ $action->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Performer -->
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="performer" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="all">All Performers</option>
                        <option value="user">User Transactions</option>
                        <option value="system">System Transactions</option>
                    </select>
                </div>

                <!-- Reset -->
                <div class="col-span-12 sm:col-span-6 md:col-span-2 flex items-center justify-end">
                    <button wire:click="resetFilters" class="text-xs text-slate-500 hover:text-primary underline flex items-center">
                        <i data-lucide="rotate-ccw" class="w-3 h-3 mr-1"></i> Clear Filters
                    </button>
                </div>

                <!-- Date Range -->
                <div class="col-span-12 md:col-span-6 flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] uppercase font-bold text-slate-400">From</span>
                        <input wire:model.live="startDate" type="date" class="w-full pl-12 h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                    </div>
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] uppercase font-bold text-slate-400">To</span>
                        <input wire:model.live="endDate" type="date" class="w-full pl-12 h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="overflow-x-auto overflow-y-hidden">
    <table class="w-full text-left">
        <thead class="border-b border-slate-200/60 transition-colors dark:border-darkmode-400">
            <tr>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">User</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">Action Type</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">Amount</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">Prev. Balance</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">Curr. Balance</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">Description</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-center">Status</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-right">Date</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-center"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($this->transactions as $tx)
            <tr class="border-b border-slate-200/60 last:border-0 hover:bg-slate-50 transition-colors dark:border-darkmode-400 dark:hover:bg-darkmode-400/30">
                <td class="px-5 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center mr-3 text-slate-400">
                            <i data-lucide="{{ $tx->user_id > 0 ? 'user' : 'settings' }}" class="w-4 h-4"></i>
                        </div>
                        <div class="flex flex-col">
                            @if($tx->user_id > 0 && $tx->user)
                                <a wire:navigate href="{{ route('admin.users.show', $tx->user) }}" class="font-medium text-primary hover:underline">
                                    {{ $tx->user->name }}
                                </a>
                                <span class="text-[10px] text-slate-400">{{ $tx->user->email }}</span>
                            @else
                                <span class="font-medium text-slate-700 dark:text-slate-300">System</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                    <div class="flex flex-col">
                        <span class="capitalize text-slate-700 dark:text-slate-300 text-sm font-medium">
                            {{ str_replace('_', ' ', $tx->action?->value ?? 'unknown') }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono uppercase tracking-tight">{{ $tx->reference }}</span>
                    </div>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                    <div class="font-medium {{ in_array(strtolower($tx->type?->value ?? ''), ['credit', 'deposit']) ? 'text-success' : 'text-danger' }}">
                        {{ in_array(strtolower($tx->type?->value ?? ''), ['credit', 'deposit']) ? '+' : '-' }}{{ crypto_format($tx->amount, $tx->currency?->decimal ?? 8) }} {{ $tx->currency ? $tx->currency->symbol : '' }}
                    </div>
                    <div class="text-[10px] text-slate-400 mt-0.5">${{ crypto_format($tx->usd, 2) }}</div>
                </td>
                <td class="px-5 py-4 whitespace-nowrap font-mono text-sm text-slate-500">
                    {{ crypto_format($tx->previous_balance, $tx->currency?->decimal ?? 8) }}
                </td>
                <td class="px-5 py-4 whitespace-nowrap font-mono text-sm text-slate-700 dark:text-slate-300">
                    {{ crypto_format($tx->current_balance, $tx->currency?->decimal ?? 8) }}
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-slate-500 text-xs max-w-[200px] overflow-hidden text-ellipsis">
                    {{ $tx->description ?: '-' }}
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider {{ strtolower($tx->status?->value ?? '') == 'completed' ? 'bg-success/10 text-success' : (strtolower($tx->status?->value ?? '') == 'pending' ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger') }}">
                        {{ $tx->status?->value ?? 'pending' }}
                    </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-slate-500 text-right text-xs">
                    {{ $tx->created_at->format('M d, Y h:i A') }}
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-center">
                    <a wire:navigate href="{{ route('admin.transactions.show', $tx) }}" class="flex items-center text-primary dark:text-slate-300 hover:underline">
                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i> View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                    <div class="flex flex-col items-center justify-center">
                        <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-20"></i>
                        <p>No transactions found.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($paginated)
        <div class="px-5 py-4 border-t border-slate-200/60 dark:border-darkmode-400">
            {{ $this->transactions->links() }}
        </div>
    @endif
</div>
</div>
