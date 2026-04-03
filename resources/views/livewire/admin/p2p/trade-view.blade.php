<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <!-- Header -->
    <div class="col-span-12">
        <div class="flex flex-col items-center gap-y-3 sm:flex-row sm:h-10">
            <div class="flex items-center">
                <a wire:navigate href="{{ route('admin.p2p.trades.index') }}" class="mr-4 text-slate-500 hover:text-primary transition-colors">
                    <i data-lucide="arrow-left" class="w-6 h-6"></i>
                </a>
                <div class="text-base font-medium group-[.mode--light]:text-white">Trade Audit: #{{ $trade->id }}</div>
            </div>
            <div class="flex items-center gap-x-3 sm:ml-auto">
                <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-widest 
                    {{ $trade->status->value === 'completed' ? 'bg-success/10 text-success' : 
                       ($trade->status->value === 'disputed' ? 'bg-danger/10 text-danger animate-pulse' : 
                       ($trade->status->value === 'cancelled' ? 'bg-slate-100 text-slate-400' : 'bg-warning/10 text-warning')) }}">
                    {{ $trade->status->label() }}
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-y-6">
        <!-- Trade Financials -->
        <div class="box box--stacked p-5">
            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                <i data-lucide="info" class="w-4 h-4 text-slate-500 mr-2"></i>
                <div class="text-sm font-medium">Trade Details</div>
            </div>
            <div class="grid grid-cols-12 gap-y-6">
                <div class="col-span-12 sm:col-span-6">
                    <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Crypto Amount</div>
                    <div class="text-2xl font-bold text-slate-700 dark:text-slate-200">
                        {{ crypto_format($trade->crypto_amount, $trade->currency->decimal ?? 8) }}
                        <span class="text-sm font-normal text-slate-400">{{ $trade->currency->symbol }}</span>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Fiat Amount</div>
                    <div class="text-2xl font-bold text-primary">
                        {{ crypto_format($trade->fiat_amount, 2) }}
                        <span class="text-sm font-normal opacity-70">NGN</span>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="p-4 rounded-lg bg-slate-50 dark:bg-darkmode-400/50 border border-dashed border-slate-300 dark:border-darkmode-400">
                        <div class="text-[10px] uppercase font-bold text-slate-400 mb-2">Advertisement Terms</div>
                        <div class="text-xs text-slate-600 dark:text-slate-400 italic leading-relaxed">
                            {{ $trade->advertisement->terms ?: 'No specific terms provided for this advertisement.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Dispute Actions -->
        @if($trade->status->value === 'disputed' || $trade->status->value === 'paid')
            <div class="box box--stacked p-5 border-2 border-danger/20">
                <div class="flex items-center border-b border-danger/10 pb-5 mb-5">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-danger mr-2"></i>
                    <div class="text-sm font-bold text-danger uppercase tracking-wider">Dispute Resolution Actions</div>
                </div>
                
                @if($trade->status->value === 'disputed')
                    <div class="mb-6 p-4 rounded bg-danger/5 border border-danger/10">
                        <div class="text-xs font-bold text-danger uppercase mb-1">Conflict Reported</div>
                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            Reason: <span class="font-medium">"{{ $trade->dispute_reason ?: 'No reason provided' }}"</span>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-4">
                    <button 
                        wire:confirm="ARE YOU SURE? This will force-release the escrowed crypto to the BUYER. This action cannot be undone."
                        wire:click="releaseCrypto" 
                        class="flex-1 h-12 bg-success text-white rounded-lg font-bold shadow-lg shadow-success/20 hover:bg-success/90 transition-all flex items-center justify-center"
                    >
                        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> Release to Buyer
                    </button>
                    <button 
                        wire:confirm="ARE YOU SURE? This will cancel the trade and return the escrowed crypto to the SELLER. This action cannot be undone."
                        wire:click="cancelTrade" 
                        class="flex-1 h-12 bg-danger text-white rounded-lg font-bold shadow-lg shadow-danger/20 hover:bg-danger/90 transition-all flex items-center justify-center"
                    >
                        <i data-lucide="x-circle" class="w-5 h-5 mr-2"></i> Cancel & Refund Seller
                    </button>
                </div>
                <div class="mt-4 text-[10px] text-center text-slate-400 italic">
                    Note: Executing either action will immediately update the respective user balances and close the trade.
                </div>
            </div>
        @endif
    </div>

    <!-- Side Content -->
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-y-6">
        <!-- Participants -->
        <div class="box box--stacked p-5">
            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                <i data-lucide="users" class="w-4 h-4 text-slate-500 mr-2"></i>
                <div class="text-sm font-medium">Participants</div>
            </div>
            
            <!-- Buyer -->
            <div class="mb-6">
                <div class="text-[10px] uppercase font-bold text-slate-400 mb-3 flex items-center">
                    <span class="w-2 h-2 rounded-full bg-primary mr-2"></span> Buyer
                </div>
                <div class="flex items-center p-3 rounded-lg bg-slate-50 dark:bg-darkmode-400/50">
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-3 font-bold text-sm">
                        {{ $trade->buyer->initials() }}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $trade->buyer->name }}</div>
                        <div class="text-[10px] text-slate-500">{{ $trade->buyer->email }}</div>
                    </div>
                    <a wire:navigate href="{{ route('admin.users.show', $trade->buyer) }}" class="p-2 text-slate-400 hover:text-primary">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Seller -->
            <div>
                <div class="text-[10px] uppercase font-bold text-slate-400 mb-3 flex items-center">
                    <span class="w-2 h-2 rounded-full bg-warning mr-2"></span> Seller (Escrow)
                </div>
                <div class="flex items-center p-3 rounded-lg bg-slate-50 dark:bg-darkmode-400/50">
                    <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center mr-3 font-bold text-sm">
                        {{ $trade->seller->initials() }}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $trade->seller->name }}</div>
                        <div class="text-[10px] text-slate-500">{{ $trade->seller->email }}</div>
                    </div>
                    <a wire:navigate href="{{ route('admin.users.show', $trade->seller) }}" class="p-2 text-slate-400 hover:text-primary">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Ad Context -->
        <div class="box box--stacked p-5">
            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                <i data-lucide="megaphone" class="w-4 h-4 text-slate-500 mr-2"></i>
                <div class="text-sm font-medium">Source Advertisement</div>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-xs text-slate-500">Ad Type</span>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase underline">{{ $trade->advertisement->type->label() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-slate-500">Post Creator</span>
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $trade->advertisement->user->name }}</span>
                </div>
                <div class="pt-4 border-t border-dashed border-slate-200 dark:border-darkmode-400">
                    <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Reference ID</div>
                    <code class="text-[10px] text-slate-500 font-mono">{{ $trade->advertisement->id }}</code>
                </div>
            </div>
        </div>
    </div>
</div>
