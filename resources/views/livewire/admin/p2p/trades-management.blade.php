<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <!-- Header -->
    <div class="col-span-12">
        <div class="flex flex-col items-center gap-y-3 sm:flex-row sm:h-10">
            <div class="flex items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white uppercase tracking-wider">P2P Trades Ledger</div>
                @if($adId)
                    <div class="ml-4 flex items-center bg-primary/10 text-primary px-3 py-1 rounded-full text-[10px] font-bold">
                        Filtering by Ad #{{ $adId }}
                        <button wire:click="$set('adId', null)" class="ml-2 hover:text-danger">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="col-span-12">
        <div class="box box--stacked p-5">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-6">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search buyer or seller name..." class="w-full pl-10 h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-4">
                    <select wire:model.live="status" class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        <option value="">All Statuses</option>
                        @foreach(\App\Enum\TradeStatus::cases() as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-6 md:col-span-2 flex items-center justify-end">
                    <button wire:click="resetFilters" class="text-xs text-slate-500 hover:text-primary underline">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="col-span-12">
        <div class="box box--stacked p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-darkmode-400/50">
                            <th class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Parties</th>
                            <th class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500">Asset & Amount</th>
                            <th class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right">Fiat Value</th>
                            <th class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-center">Status</th>
                            <th class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-right">Date</th>
                            <th class="px-5 py-4 border-b border-slate-200/60 dark:border-darkmode-400 text-[11px] font-bold uppercase text-slate-500 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 dark:divide-darkmode-400">
                        @forelse($this->trades as $trade)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-darkmode-400/20 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex flex-col gap-y-2">
                                        <div class="flex items-center">
                                            <span class="text-[10px] w-12 font-bold text-slate-400 uppercase">Buyer:</span>
                                            <div class="flex items-center">
                                                <div class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-2 text-[8px] font-bold">
                                                    {{ $trade->buyer->initials() }}
                                                </div>
                                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $trade->buyer->name }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-[10px] w-12 font-bold text-slate-400 uppercase">Seller:</span>
                                            <div class="flex items-center">
                                                <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center mr-2 text-[8px] font-bold">
                                                    {{ $trade->seller->initials() }}
                                                </div>
                                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $trade->seller->name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                            {{ crypto_format($trade->crypto_amount, $trade->currency->decimal ?? 8) }}
                                            <span class="text-xs font-normal text-slate-500 uppercase">{{ $trade->currency->symbol }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 lowercase italic">
                                            Ref: {{ $trade->advertisement_id }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right font-mono text-sm">
                                    {{ crypto_format($trade->fiat_amount, 2) }}
                                    <span class="text-[10px] text-slate-500 uppercase">NGN</span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider 
                                        {{ $trade->status->value === 'completed' ? 'bg-success/10 text-success' : 
                                           ($trade->status->value === 'disputed' ? 'bg-danger/10 text-danger animate-pulse' : 
                                           ($trade->status->value === 'cancelled' ? 'bg-slate-100 text-slate-400' : 'bg-warning/10 text-warning')) }}">
                                        {{ $trade->status->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs text-slate-500">
                                    {{ $trade->created_at->format('M d, Y') }}
                                    <div class="text-[10px] opacity-60 font-mono">{{ $trade->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <a wire:navigate href="{{ route('admin.p2p.trades.show', $trade) }}" class="flex items-center text-primary hover:underline text-xs">
                                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i> Audit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400 italic">
                                    <div class="flex flex-col items-center justify-center opacity-50">
                                        <i data-lucide="inbox" class="w-12 h-12 mb-3"></i>
                                        <p>No P2P trades matching your selection were found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $this->trades->links() }}
        </div>
    </div>
</div>
