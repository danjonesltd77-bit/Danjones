<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <!-- Header -->
    <div class="col-span-12">
        <div class="flex flex-col items-center gap-y-3 sm:flex-row sm:h-10">
            <div class="flex items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white uppercase tracking-wider">P2P
                    Advertisements</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="col-span-12">
        <div class="box box--stacked p-5">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-4">
                    <div class="relative">
                        <i data-lucide="search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="Search user or amount..."
                            class="w-full pl-10 h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="type"
                        class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Types</option>
                        <option value="buy">Buy</option>
                        <option value="sell">Sell</option>
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="currencyId"
                        class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Currencies</option>
                        @foreach ($this->currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->symbol }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2">
                    <select wire:model.live="status"
                        class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2 flex items-center justify-end">
                    <button wire:click="resetFilters" class="text-xs text-slate-500 hover:text-primary underline">Clear
                        Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="col-span-12 overflow-x-auto">
        <div class="box box--stacked p-0 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-darkmode-400/50">
                        <th
                            class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">
                            User</th>
                        <th
                            class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">
                            Type</th>
                        <th
                            class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-center">
                            Trades</th>
                        <th
                            class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right">
                            Price</th>
                        <th
                            class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right">
                            Available</th>
                        <th
                            class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-center">
                            Status</th>
                        <th
                            class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right">
                            Created</th>
                        <th
                            class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-center">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 dark:divide-darkmode-400">
                    @forelse($this->ads as $ad)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-darkmode-400/20 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-3 text-[10px] font-bold">
                                        {{ $ad->user->initials() }}
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {{ $ad->user->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $ad->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $ad->type->value === 'buy' ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                    {{ $ad->type->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                    {{ $ad->trades_count }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                    {{ crypto_format($ad->price, 2) }}
                                    <span class="text-[10px] font-normal text-slate-500 lowercase">per
                                        {{ $ad->currency->symbol }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ crypto_format($ad->available_amount, $ad->currency->decimal ?? 8) }}
                                    <span class="text-[10px] text-slate-400">{{ $ad->currency->symbol }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400">Limits: {{ crypto_format($ad->min_limit) }} -
                                    {{ crypto_format($ad->max_limit) }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $ad->is_active ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $ad->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right text-xs text-slate-500">
                                {{ $ad->created_at->format('M d, Y') }}
                                <div class="text-[10px] opacity-60">{{ $ad->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a wire:navigate href="{{ route('admin.p2p.trades.index', ['adId' => $ad->id]) }}"
                                        class="p-2 text-slate-400 hover:text-primary transition-colors"
                                        title="View Trade History">
                                        <i data-lucide="list" class="w-4 h-4"></i>
                                    </a>
                                    <button
                                        wire:confirm="Are you sure you want to change the status of this advertisement? If disabled, users will no longer be able to initiate new trades with it."
                                        wire:click="toggleStatus({{ $ad->id }})"
                                        class="p-2 rounded-full transition-colors {{ $ad->is_active ? 'text-danger hover:bg-danger/10' : 'text-success hover:bg-success/10' }}"
                                        title="{{ $ad->is_active ? 'Deactivate Ad' : 'Activate Ad' }}">
                                        <i data-lucide="{{ $ad->is_active ? 'x-circle' : 'check-circle' }}"
                                            class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400 italic">
                                <div class="flex flex-col items-center justify-center opacity-50">
                                    <i data-lucide="inbox" class="w-12 h-12 mb-3"></i>
                                    <p>No advertisements matching your selection were found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $this->ads->links() }}
        </div>
    </div>
</div>
