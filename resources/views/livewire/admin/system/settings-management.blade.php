<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <!-- Header -->
    <div class="col-span-12">
        <div class="flex flex-col items-center gap-y-3 sm:flex-row sm:h-10">
            <div class="flex items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white uppercase tracking-wider">
                    System Configuration Settings
                </div>
            </div>
            <div class="sm:ml-auto flex items-center gap-2">
                <button wire:click="openCreateModal"
                    class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 flex items-center shadow-lg shadow-primary/20 transition-all">
                    <i class="bi bi-plus-circle mr-2 text-xs"></i> Register New Key
                </button>
            </div>
        </div>
    </div>

    <!-- Filters & Settings List -->
    <div class="col-span-12">
        <div class="box box--stacked p-5">
            <!-- Search & Tabs Header -->
            <div
                class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6 pb-6 border-b border-dashed border-slate-200">
                <!-- Group Tabs -->
                <div class="flex items-center gap-2 p-1 bg-slate-100/80 rounded-lg">
                    <button wire:click="$set('activeTab', 'all')"
                        class="px-4 py-1.5 rounded-md text-xs font-bold transition-all uppercase tracking-wider {{ $activeTab === 'all' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        <span class="flex items-center">
                            <i class="bi bi-sliders mr-1.5 text-xs"></i> All Settings
                        </span>
                    </button>
                </div>

                <!-- Search Input -->
                <div
                    class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 w-full md:w-80 shadow-inner">
                    <i class="bi bi-search text-slate-400 text-xs"></i>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Filter settings by key or desc..."
                        class="bg-transparent border-0 p-0 text-xs focus:ring-0 w-full placeholder:text-slate-400 text-slate-700">
                </div>
            </div>

            <!-- Settings Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-dashed border-slate-300/80">
                            <th
                                class="px-5 py-3 font-semibold text-slate-500 uppercase text-[10px] tracking-widest w-1/3">
                                Setting Key</th>
                            <th
                                class="px-5 py-3 font-semibold text-slate-500 uppercase text-[10px] tracking-widest w-1/4">
                                Value</th>
                            <th
                                class="px-5 py-3 font-semibold text-slate-500 uppercase text-[10px] tracking-widest text-center">
                                Type</th>
                            <th
                                class="px-5 py-3 font-semibold text-slate-500 uppercase text-[10px] tracking-widest w-1/4">
                                Description</th>
                            <th
                                class="px-5 py-3 font-semibold text-slate-500 uppercase text-[10px] tracking-widest text-right">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($settings as $setting)
                            <tr
                                class="border-b border-dashed border-slate-200/80 last:border-0 hover:bg-slate-50/50 transition-colors">
                                <!-- Key -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-key text-slate-400 text-xs"></i>
                                        <span
                                            class="text-xs font-mono font-bold text-slate-700 bg-slate-100 border border-slate-200/60 px-2 py-1 rounded select-all">{{ $setting->key }}</span>
                                    </div>
                                </td>

                                <!-- Value -->
                                <td class="px-5 py-4">
                                    @if ($editingSettingId === $setting->id)
                                        <div class="flex items-center gap-2">
                                            <input wire:model="editingValue" type="text"
                                                class="h-8 px-3 bg-white border border-slate-300 rounded-md text-xs focus:ring-1 focus:ring-primary focus:border-primary w-full text-slate-700 font-mono shadow-sm">
                                            <button wire:click="saveEdit({{ $setting->id }})"
                                                class="p-1.5 bg-success text-white rounded hover:bg-success/90 shadow transition-colors"
                                                title="Save">
                                                <i class="bi bi-check-lg text-xs"></i>
                                            </button>
                                            <button wire:click="cancelEdit"
                                                class="p-1.5 bg-slate-200 text-slate-600 rounded hover:bg-slate-300 transition-colors"
                                                title="Cancel">
                                                <i class="bi bi-x-lg text-xs"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="flex items-center group/value">
                                            <span
                                                class="text-xs font-mono font-bold text-slate-800 bg-primary/5 border border-primary/10 px-2.5 py-1 rounded shadow-inner min-w-[50px] text-center select-all">
                                                {{ $setting->value }}
                                            </span>
                                            <button wire:click="startEdit({{ $setting->id }})"
                                                class="ml-2 text-slate-400 hover:text-primary transition-colors opacity-0 group-hover/value:opacity-100">
                                                <i class="bi bi-pencil text-[10px]"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>

                                <!-- Type -->
                                <td class="px-5 py-4 text-center">
                                    @php
                                        $typeColor = match ($setting->type) {
                                            'float' => 'bg-emerald-50 text-emerald-600 border-emerald-200/60',
                                            'integer' => 'bg-blue-50 text-blue-600 border-blue-200/60',
                                            'boolean' => 'bg-indigo-50 text-indigo-600 border-indigo-200/60',
                                            default => 'bg-amber-50 text-amber-600 border-amber-200/60',
                                        };
                                    @endphp
                                    <span
                                        class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border {{ $typeColor }}">
                                        {{ $setting->type }}
                                    </span>
                                </td>

                                <!-- Description -->
                                <td class="px-5 py-4">
                                    <div class="text-slate-500 text-xs italic line-clamp-2"
                                        title="{{ $setting->description }}">
                                        {{ $setting->description ?? 'No description registered for this key.' }}
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        @if ($editingSettingId !== $setting->id)
                                            <button wire:click="startEdit({{ $setting->id }})"
                                                class="text-slate-400 hover:text-primary transition-colors"
                                                title="Edit Setting">
                                                <i class="bi bi-pencil-square text-sm"></i>
                                            </button>
                                        @endif
                                        <button wire:click="deleteSetting({{ $setting->id }})"
                                            onclick="confirm('Are you sure you want to completely delete this configuration key? Doing so may cause system errors if hardcoded.') || event.stopImmediatePropagation()"
                                            class="text-slate-400 hover:text-danger transition-colors"
                                            title="Delete Setting">
                                            <i class="bi bi-trash3 text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-40">
                                        <i class="bi bi-gear text-4xl mb-3 text-slate-300/80"></i>
                                        <div class="text-sm font-bold text-slate-400">No Config Settings Found</div>
                                        <div class="text-[11px] text-slate-400 italic">Try adjusting your filters or
                                            register a new setting key.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Setting Modal -->
    @if ($showCreateModal)
        <div
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="box box--stacked w-full max-w-lg shadow-2xl animate-in zoom-in-95 duration-200">
                <div class="p-5 border-b border-slate-200/60 flex items-center justify-between bg-slate-50/50">
                    <div class="text-base font-bold text-slate-800 uppercase tracking-tight flex items-center">
                        <i class="bi bi-plus-circle text-lg mr-2 text-primary"></i> Register New Config Key
                    </div>
                    <button wire:click="closeCreateModal" class="text-slate-400 hover:text-danger">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>

                <form wire:submit.prevent="createSetting">
                    <div class="p-6 flex flex-col gap-y-5">
                        <!-- Key -->
                        <div>
                            <label
                                class="text-[10px] uppercase font-bold text-slate-400 block mb-2 tracking-widest">Setting
                                Key</label>
                            <input wire:model="newKey" type="text"
                                class="w-full h-10 px-4 bg-slate-50 border-slate-200 rounded-lg text-xs focus:ring-primary focus:border-primary font-mono text-slate-800"
                                placeholder="e.g. cashback_bill_electricity">
                            @error('newKey')
                                <span class="text-[10px] text-danger mt-1 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Type & Value Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-[10px] uppercase font-bold text-slate-400 block mb-2 tracking-widest">Type</label>
                                <select wire:model="newType"
                                    class="w-full h-10 px-3 bg-slate-50 border-slate-200 rounded-lg text-xs focus:ring-primary">
                                    <option value="float">Float (Numeric decimal)</option>
                                    <option value="integer">Integer (Whole number)</option>
                                    <option value="string">String (Text)</option>
                                    <option value="boolean">Boolean (true/false)</option>
                                </select>
                                @error('newType')
                                    <span
                                        class="text-[10px] text-danger mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="text-[10px] uppercase font-bold text-slate-400 block mb-2 tracking-widest">Initial
                                    Value</label>
                                <input wire:model="newValue" type="text"
                                    class="w-full h-10 px-4 bg-slate-50 border-slate-200 rounded-lg text-xs focus:ring-primary font-mono text-slate-800"
                                    placeholder="e.g. 1.5">
                                @error('newValue')
                                    <span
                                        class="text-[10px] text-danger mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                class="text-[10px] uppercase font-bold text-slate-400 block mb-2 tracking-widest">Description</label>
                            <textarea wire:model="newDescription" rows="3"
                                class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-xs focus:ring-primary"
                                placeholder="State clearly what this configuration setting manages..."></textarea>
                            @error('newDescription')
                                <span class="text-[10px] text-danger mt-1 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="p-5 bg-slate-50/50 flex justify-end gap-x-3 border-t border-slate-200/60">
                        <button type="button" wire:click="closeCreateModal"
                            class="px-5 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors uppercase">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 shadow-lg shadow-primary/20 flex items-center disabled:opacity-50">
                            <span wire:loading.remove wire:target="createSetting" class="flex items-center">
                                <i class="bi bi-check-lg mr-2 text-xs"></i> Register Key
                            </span>
                            <span wire:loading wire:target="createSetting" class="flex items-center italic">
                                Registering...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
