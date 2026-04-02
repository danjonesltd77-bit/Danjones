<div class="overflow-x-auto overflow-y-hidden">
    <table class="w-full text-left">
        <thead class="border-b border-slate-200/60 transition-colors dark:border-darkmode-400">
            <tr>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">User</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">Type</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">Amount</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-center">Status</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-right">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($this->transactions as $tx)
            <tr class="border-b border-slate-200/60 last:border-0 hover:bg-slate-50 transition-colors dark:border-darkmode-400 dark:hover:bg-darkmode-400/30">
                <td class="px-5 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center mr-3 text-slate-400">
                            <i data-lucide="{{ $tx->user ? 'user' : 'settings' }}" class="w-4 h-4"></i>
                        </div>
                        <div class="font-medium text-slate-700 dark:text-slate-300">
                            {{ $tx->user ? trim($tx->user->name) : 'System' }}
                        </div>
                    </div>
                </td>
                <td class="px-5 py-4 whitespace-nowrap capitalize text-slate-500 text-sm">
                    {{ str_replace('_', ' ', $tx->type?->value ?? 'unknown') }}
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                    <div class="font-medium {{ in_array(strtolower($tx->type?->value ?? ''), ['credit', 'deposit']) ? 'text-success' : 'text-danger' }}">
                        {{ in_array(strtolower($tx->type?->value ?? ''), ['credit', 'deposit']) ? '+' : '-' }}{{ number_format($tx->amount, 8) }} {{ $tx->currency ? $tx->currency->symbol : '' }}
                    </div>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider {{ strtolower($tx->status?->value ?? '') == 'completed' ? 'bg-success/10 text-success' : (strtolower($tx->status?->value ?? '') == 'pending' ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger') }}">
                        {{ $tx->status?->value ?? 'pending' }}
                    </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-slate-500 text-right text-xs">
                    {{ $tx->created_at->format('M d, Y H:i') }}
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
</div>
