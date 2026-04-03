<div>
    <div class="grid grid-cols-12 gap-x-6 gap-y-6">
        <!-- Header -->
        <div class="col-span-12">
            <div class="flex flex-col items-center gap-y-3 sm:flex-row sm:h-10">
                <div class="flex items-center">
                    <a wire:navigate href="{{ route('admin.transactions.index') }}"
                        class="mr-4 text-slate-500 hover:text-primary transition-colors">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>
                    <div class="text-base font-medium group-[.mode--light]:text-white">Transaction Details</div>
                </div>
                <div class="flex items-center gap-x-3 sm:ml-auto">
                    <div
                        class="px-3 py-1 text-xs font-bold tracking-wider uppercase rounded-full {{ strtolower($transaction->status?->value ?? '') == 'completed' ? 'bg-success/10 text-success' : (in_array(strtolower($transaction->status?->value ?? ''), ['pending', 'processing']) ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger') }}">
                        {{ $transaction->status?->label() ?? 'Pending' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Details -->
        <div class="col-span-12 md:col-span-8">
            <div class="box box--stacked p-6">
                <h3
                    class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-6 pb-4 border-b border-slate-200 dark:border-darkmode-400">
                    Transaction Overview
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                    <!-- Action Type -->
                    <div>
                        <span class="block text-sm text-slate-500 mb-1">Action Type</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200 capitalize">
                            {{ str_replace('_', ' ', $transaction->action?->value ?? 'System') }}
                        </span>
                    </div>

                    <!-- Reference -->
                    <div>
                        <span class="block text-sm text-slate-500 mb-1">Reference ID</span>
                        <span class="font-mono text-slate-800 dark:text-slate-200">
                            {{ $transaction->reference }}
                        </span>
                    </div>

                    <!-- Amount -->
                    <div>
                        <span class="block text-sm text-slate-500 mb-1">Amount</span>
                        <div
                            class="font-bold text-lg {{ in_array(strtolower($transaction->type?->value ?? ''), ['credit', 'deposit']) ? 'text-success' : 'text-danger' }}">
                            {{ in_array(strtolower($transaction->type?->value ?? ''), ['credit', 'deposit']) ? '+' : '-' }}{{ crypto_format($transaction->amount, $transaction->currency?->decimal ?? 8) }}
                            <span class="text-sm font-normal text-slate-500">{{ $transaction->currency?->symbol }}</span>
                        </div>
                    </div>

                    <!-- USD Value -->
                    <div>
                        <span class="block text-sm text-slate-500 mb-1">USD Value</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">
                            ${{ crypto_format($transaction->usd, 2) }}
                        </span>
                    </div>

                    <!-- Charge / Fee -->
                    <div>
                        <span class="block text-sm text-slate-500 mb-1">Network / Transaction Charge</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">
                            @php
                                $charge = $transaction->metadata['charge'] ?? ($transaction->metadata['fee'] ?? 0);
                            @endphp
                            {{ crypto_format((float) $charge, $transaction->currency?->decimal ?? 8) }} <span
                                class="text-xs text-slate-500">{{ $transaction->currency?->symbol }}</span>
                        </span>
                    </div>

                    <!-- Asset -->
                    <div>
                        <span class="block text-sm text-slate-500 mb-1">Asset Wallet</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">
                            {{ $transaction->wallet?->currency?->name ?? 'System Wallet' }}
                        </span>
                    </div>

                    <!-- Ledger Progression -->
                    <div
                        class="col-span-1 sm:col-span-2 mt-2 pt-6 border-t border-dashed border-slate-200 dark:border-darkmode-400">
                        <span class="block text-sm text-slate-500 mb-3">Balance Progression</span>
                        <div class="flex items-center gap-4 text-sm">
                            <div
                                class="bg-slate-50 dark:bg-darkmode-400 px-3 py-2 rounded-md font-mono text-slate-600 dark:text-slate-300">
                                {{ crypto_format($transaction->previous_balance, $transaction->currency?->decimal ?? 8) }}
                            </div>
                            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
                            <div
                                class="bg-slate-50 dark:bg-darkmode-400 px-3 py-2 rounded-md font-mono font-bold {{ in_array(strtolower($transaction->type?->value ?? ''), ['credit', 'deposit']) ? 'text-success' : 'text-danger' }}">
                                {{ crypto_format($transaction->current_balance, $transaction->currency?->decimal ?? 8) }}
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-span-1 sm:col-span-2 pt-2">
                        <span class="block text-sm text-slate-500 mb-1">Description</span>
                        <p
                            class="text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50/50 dark:bg-darkmode-400/50 p-4 rounded-lg">
                            {{ $transaction->description ?: 'No specific description provided.' }}
                        </p>
                    </div>
                </div>
            </div>

            @if (count($this->relatedTransactions['user']) > 0)
                <div class="box box--stacked p-6 mt-6">
                    <h3 class="text-base font-medium text-slate-800 dark:text-slate-200 mb-4 flex items-center">
                        <i data-lucide="link" class="w-4 h-4 mr-2 text-primary"></i>
                        Linked Payments & Transfers
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach ($this->relatedTransactions['user'] as $related)
                            <div class="p-4 rounded-lg border border-dashed border-slate-200 dark:border-darkmode-400 group">
                                <div
                                    class="flex items-center justify-between mb-3 pb-3 border-b border-dashed border-slate-200 dark:border-darkmode-400">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 rounded-full bg-slate-100 dark:bg-darkmode-400 flex items-center justify-center mr-3 text-slate-400">
                                            <i data-lucide="{{ $related->type->value == 'credit' ? 'arrow-down-left' : 'arrow-up-right' }}"
                                                class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 capitalize">
                                                {{ str_replace('_', ' ', $related->action->value) }}</div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ $related->created_at->format('M d, Y h:i A') }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-x-4">
                                        @if($related->user)
                                            <div class="flex items-center text-right">
                                                <div class="mr-3">
                                                    <div class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $related->user->name }}</div>
                                                    <div class="text-[10px] text-slate-400">{{ $related->user->email }}</div>
                                                </div>
                                                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[10px] font-bold">
                                                    {{ $related->user->initials() }}
                                                </div>
                                            </div>
                                        @endif
                                        <a wire:navigate href="{{ route('admin.transactions.show', $related) }}"
                                            class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-darkmode-400 text-primary transition-colors"
                                            title="View Full Details">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[10px] text-slate-400 uppercase tracking-wider">Asset
                                            Value</span>
                                        <div
                                            class="text-sm font-bold {{ $related->type->value == 'credit' ? 'text-success' : 'text-danger' }}">
                                            {{ $related->type->value == 'credit' ? '+' : '-' }}{{ crypto_format($related->amount, $related->currency?->decimal ?? 8) }}
                                            <span class="text-[10px] text-slate-500">{{ $related->currency->symbol }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-medium">
                                            ${{ crypto_format($related->usd, 2) }}</div>
                                    </div>
                                    <div class="flex flex-col gap-1 items-end">
                                        <span class="text-[10px] text-slate-400 uppercase tracking-wider">Progression</span>
                                        <div class="flex items-center gap-2 text-[11px] font-mono">
                                            <span
                                                class="text-slate-400">{{ crypto_format($related->previous_balance, $related->currency?->decimal ?? 8) }}</span>
                                            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                                            <span
                                                class="font-bold text-slate-600 dark:text-slate-300">{{ crypto_format($related->current_balance, $related->currency?->decimal ?? 8) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (count($this->relatedTransactions['system']) > 0)
                <div class="box box--stacked p-6 mt-6 bg-slate-50/30 dark:bg-darkmode-400/20">
                    <h3 class="text-base font-medium text-slate-800 dark:text-slate-200 mb-4 flex items-center">
                        <i data-lucide="shield-check" class="w-4 h-4 mr-2 text-warning"></i>
                        Internal Operations & Fees
                    </h3>
                    <div class="grid grid-cols-1 gap-3">
                        @foreach ($this->relatedTransactions['system'] as $related)
                            <div
                                class="flex items-center justify-between p-3 rounded-lg bg-white dark:bg-darkmode-600 border border-slate-200 dark:border-darkmode-400">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 rounded-full bg-warning/10 flex items-center justify-center mr-3 text-warning">
                                        <i data-lucide="zap" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300 capitalize">
                                            {{ str_replace('_', ' ', $related->action->value) }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            {{ $related->created_at->format('h:i A') }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-danger">
                                        -{{ crypto_format($related->amount, $related->currency?->decimal ?? 8) }} <span
                                            class="text-[10px] text-slate-400">{{ $related->currency->symbol }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium">
                                        ${{ crypto_format($related->usd, 2) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Info -->
        <div class="col-span-12 md:col-span-4 flex flex-col gap-y-6">
            <!-- User Context -->
            @if ($transaction->user_id > 0 && $transaction->user)
                <div class="box box--stacked p-6">
                    <h3 class="text-slate-500 font-medium mb-4 flex items-center">
                        User Information
                    </h3>
                    <div class="flex items-center">
                        <div
                            class="h-10 w-10 flex items-center justify-center rounded-full bg-primary/10 text-primary font-bold">
                            {{ $transaction->user->initials() }}
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ $transaction->user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $transaction->user->email }}</div>
                        </div>
                    </div>
                    <div class="mt-5 pt-4 border-t border-slate-200 dark:border-darkmode-400">
                        <a wire:navigate href="{{ route('admin.users.show', $transaction->user) }}"
                            class="flex items-center justify-center w-full py-2 border border-slate-300 dark:border-darkmode-400 rounded-md text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-darkmode-400 transition-colors text-sm">
                            View Full Profile
                        </a>
                    </div>
                </div>
            @endif

            <!-- Timeline Info -->
            <div class="box box--stacked p-6">
                <h3 class="text-slate-500 font-medium mb-4">
                    Timeline
                </h3>
                <div class="flex flex-col gap-3">
                    <div>
                        <span class="block text-xs text-slate-500 mb-0.5">Created At</span>
                        <span
                            class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $transaction->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    @if ($transaction->updated_at->ne($transaction->created_at))
                        <div>
                            <span class="block text-xs text-slate-500 mb-0.5">Last Updated</span>
                            <span
                                class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $transaction->updated_at->format('M d, Y h:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Raw Metadata Collapse -->
            @if (!empty($transaction->metadata))
                <div class="box box--stacked p-6">
                    <h3 class="text-slate-500 font-medium mb-4">
                        Developer Metadata
                    </h3>
                    <div
                        class="bg-slate-50 dark:bg-darkmode-400 rounded-md p-4 font-mono text-xs overflow-x-auto text-slate-600 dark:text-slate-400 max-h-64 overflow-y-auto">
                        <pre class="whitespace-pre-wrap break-words">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
