<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">System Logs</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">View and manage application logs.</p>
        </div>

        <div class="flex items-center gap-2">
            <flux:select wire:model.live="selectedFile" class="min-w-[200px]">
                @foreach ($this->logFiles as $file)
                    <flux:select.option value="{{ $file }}">{{ $file }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:button wire:click="clearLogs" variant="danger" icon="trash" wire:confirm="Are you sure you want to clear this log file?">
                Clear
            </flux:button>
        </div>
    </div>

    <flux:card class="overflow-hidden! dark:bg-zinc-900/50 dark:border-zinc-800">
        <div class="flex flex-col gap-4 p-4 border-b border-slate-200 dark:border-zinc-800 md:flex-row md:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search logs..." icon="magnifying-glass" />
            </div>
            
            <div class="flex items-center gap-2">
                <flux:select wire:model.live="level" class="min-w-[120px]">
                    <flux:select.option value="all">All Levels</flux:select.option>
                    <flux:select.option value="debug">DEBUG</flux:select.option>
                    <flux:select.option value="info">INFO</flux:select.option>
                    <flux:select.option value="notice">NOTICE</flux:select.option>
                    <flux:select.option value="warning">WARNING</flux:select.option>
                    <flux:select.option value="error">ERROR</flux:select.option>
                    <flux:select.option value="critical">CRITICAL</flux:select.option>
                    <flux:select.option value="alert">ALERT</flux:select.option>
                    <flux:select.option value="emergency">EMERGENCY</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="perPage" class="min-w-[100px]">
                    <flux:select.option value="50">50</flux:select.option>
                    <flux:select.option value="100">100</flux:select.option>
                    <flux:select.option value="250">250</flux:select.option>
                    <flux:select.option value="500">500</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-100/50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-slate-600 uppercase dark:text-zinc-400 border-b border-slate-200 dark:border-zinc-800">Level</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-slate-600 uppercase dark:text-zinc-400 border-b border-slate-200 dark:border-zinc-800">Time</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-slate-600 uppercase dark:text-zinc-400 border-b border-slate-200 dark:border-zinc-800">Env</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-slate-600 uppercase dark:text-zinc-400 border-b border-slate-200 dark:border-zinc-800">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-100/30 dark:hover:bg-zinc-800/30 transition-colors group">
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $levelClass = match(strtolower($log['level'])) {
                                        'error', 'critical', 'alert', 'emergency' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border border-red-200 dark:border-red-900/50',
                                        'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50',
                                        'info', 'notice' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50',
                                        default => 'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $levelClass }}">
                                    {{ $log['level'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-mono text-slate-600 dark:text-zinc-400!">
                                {{ $log['date'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs font-medium text-slate-700 dark:text-zinc-300!">{{ $log['env'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="text-sm text-slate-900 dark:text-zinc-100! line-clamp-2" title="{{ $log['message'] }}">
                                        {{ Str::limit($log['message'], 200) }}
                                    </span>
                                    @if (strlen($log['message']) > 200)
                                        <button x-data @click="$dispatch('open-modal', { id: 'log-detail-{{ $loop->index }}' })" class="text-xs text-blue-700 dark:text-blue-300! font-semibold hover:underline mt-1 w-max">
                                            View Full Details
                                        </button>
                                        
                                        <flux:modal id="log-detail-{{ $loop->index }}" class="md:max-w-4xl">
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between">
                                                    <h3 class="text-lg font-bold text-slate-900 dark:text-zinc-100">Log Entry Details</h3>
                                                    <span class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $levelClass }}">
                                                        {{ $log['level'] }}
                                                    </span>
                                                </div>
                                                <div class="p-4 bg-slate-900 dark:bg-zinc-950 border border-slate-800 dark:border-zinc-800 rounded-lg overflow-auto max-h-[70vh] shadow-inner">
                                                    <pre class="text-xs font-mono text-blue-300 dark:text-blue-300! whitespace-pre-wrap leading-relaxed">{{ $log['full'] }}</pre>
                                                </div>
                                            </div>
                                        </flux:modal>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-600 dark:text-zinc-500 italic">
                                No logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-zinc-800">
                {{ $logs->links() }}
            </div>
        @endif
    </flux:card>
</div>
