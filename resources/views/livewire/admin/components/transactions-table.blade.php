<div class="overflow-x-auto">
    <table class="w-full text-left">
        <thead class="border-b border-slate-200/60 font-medium text-slate-500 dark:border-darkmode-400">
            <tr>
                <th class="px-5 py-3 border-b-2 dark:border-darkmode-400 whitespace-nowrap">User</th>
                <th class="px-5 py-3 border-b-2 dark:border-darkmode-400 whitespace-nowrap">Type</th>
                <th class="px-5 py-3 border-b-2 dark:border-darkmode-400 whitespace-nowrap">Amount</th>
                <th class="px-5 py-3 border-b-2 dark:border-darkmode-400 whitespace-nowrap">Status</th>
                <th class="px-5 py-3 border-b-2 dark:border-darkmode-400 whitespace-nowrap">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($this->transactions as $tx)
            <tr class="border-b border-slate-200/60 last:border-0 dark:border-darkmode-400">
                <td class="px-5 py-3 whitespace-nowrap">
                    <div class="font-medium truncate">{{ $tx->user ? trim($tx->user->first_name . ' ' . $tx->user->last_name) : 'System' }}</div>
                </td>
                <td class="px-5 py-3 whitespace-nowrap capitalize text-slate-500">
                    {{ str_replace('_', ' ', $tx->type?->value ?? 'unknown') }}
                </td>
                <td class="px-5 py-3 whitespace-nowrap">
                    <div class="font-medium {{ in_array(strtolower($tx->type?->value ?? ''), ['credit', 'deposit']) ? 'text-success' : 'text-danger' }}">
                        {{ in_array(strtolower($tx->type?->value ?? ''), ['credit', 'deposit']) ? '+' : '-' }}{{ number_format($tx->amount, 8) }} {{ $tx->currency ? $tx->currency->symbol : '' }}
                    </div>
                </td>
                <td class="px-5 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-xs capitalize {{ strtolower($tx->status?->value ?? '') == 'completed' ? 'bg-success/10 text-success' : (strtolower($tx->status?->value ?? '') == 'pending' ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger') }}">
                        {{ $tx->status?->value ?? 'pending' }}
                    </span>
                </td>
                <td class="px-5 py-3 whitespace-nowrap text-slate-500 text-sm">
                    {{ $tx->created_at->format('M d, Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-5 text-center text-slate-500">No transactions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
