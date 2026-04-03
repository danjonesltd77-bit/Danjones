<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col items-center gap-y-3 sm:flex-row sm:h-10">
            <div class="flex items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white uppercase tracking-wider">All
                    Currencies</div>
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <div class="p-5 flex flex-col gap-y-5">
            <div class="box box--stacked p-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-4">
                        <div class="relative">
                            <i data-lucide="search"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                placeholder="Search name or symbol..."
                                class="w-full pl-10 h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 md:col-span-2">
                        <select wire:model.live="type"
                            class="w-full h-10 bg-slate-50 dark:bg-darkmode-400 border-none rounded-lg text-sm focus:ring-1 focus:ring-primary">
                            <option value="">All Types</option>
                            <option value="crypto">Crypto Only</option>
                            <option value="fiat">Fiat Only</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6 md:col-span-2 flex items-center justify-end ml-auto">
                        <button wire:click="resetFilters"
                            class="text-xs text-slate-500 hover:text-primary underline">Clear Filters</button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-6">
                @foreach ($this->currencies as $currency)
                    <a wire:navigate href="{{ route('admin.currencies.show', $currency->id) }}"
                        class="col-span-12 sm:col-span-6 xl:col-span-4 box box--stacked flex flex-col p-5 transition-all hover:scale-[1.01] hover:cursor-pointer">
                        <div class="flex items-center">
                            <div
                                class="h-[54px] w-[54px] flex items-center justify-center rounded-full border border-primary/20 bg-slate-50 dark:bg-darkmode-400 text-primary shadow-sm">
                                @if ($currency->image)
                                    <img src="{{ $currency->image }}" class="w-8 h-8 object-contain">
                                @else
                                    <span class="text-lg font-bold">{{ strtoupper($currency->symbol[0]) }}</span>
                                @endif
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="text-lg font-medium text-slate-700 dark:text-slate-300">
                                        {{ $currency->name }}</div>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $currency->is_crypto ? 'bg-primary/10 text-primary' : 'bg-slate-100 dark:bg-darkmode-400 text-slate-500' }}">
                                        {{ $currency->is_crypto ? 'Crypto' : 'Fiat' }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-400 font-mono tracking-widest uppercase">
                                    {{ $currency->symbol }}</div>
                            </div>
                        </div>

                        <div
                            class="mt-6 flex items-center justify-between border-t border-slate-100 dark:border-darkmode-400/50 pt-4">
                            <div class="flex items-center">
                                <span
                                    class="w-2 h-2 rounded-full {{ $currency->is_active ? 'bg-success' : 'bg-danger' }} mr-2"></span>
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider {{ $currency->is_active ? 'text-success' : 'text-danger' }}">
                                    {{ $currency->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="flex items-center text-[10px] text-slate-400">
                                <i data-lucide="{{ $currency->is_gaspump ? 'check-circle' : 'minus-circle' }}"
                                    class="w-3.5 h-3.5 mr-1.5 {{ $currency->is_gaspump ? 'text-success' : 'opacity-30' }}"></i>
                                {{ $currency->is_gaspump ? 'Gaspump' : 'Standard' }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10 flex justify-center">
                {{ $this->currencies->links() }}
            </div>
        </div>
    </div>
</div>
