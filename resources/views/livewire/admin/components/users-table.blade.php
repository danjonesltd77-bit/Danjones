<div>
    @if ($showSearch)
        <div class="flex flex-col p-5 border-b sm:flex-row sm:items-center border-slate-200/60 dark:border-darkmode-400">
            <div class="relative w-full text-slate-500 sm:w-72">
                <input wire:model.live.debounce.300ms="search" type="text"
                    class="block w-full px-4 py-2 pr-10 text-sm transition duration-200 border rounded-[0.6rem] border-slate-300/80 bg-white/70 focus:ring-4 focus:ring-primary/20 focus:border-primary/50 outline-none"
                    placeholder="Search users...">
                <i data-lucide="search" class="absolute inset-y-0 right-0 w-4 h-4 my-auto mr-3"></i>
            </div>
        </div>
    @endif
    <div class="overflow-x-auto overflow-y-hidden">
        <table class="w-full text-left">
            <thead class="border-b border-slate-200/60 transition-colors dark:border-darkmode-400">
                <tr>
                    <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">Name</th>
                    <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-center">Email</th>
                    <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-center">Naira Balance</th>
                    <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-right">Joined</th>
                    <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->users as $user)
                    <tr
                        class="border-b border-slate-200/60 last:border-0 hover:bg-slate-50 transition-colors dark:border-darkmode-400 dark:hover:bg-darkmode-400/30">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center mr-3 text-slate-400">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <div class="font-medium text-slate-700 dark:text-slate-300">{{ trim($user->name) }}
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-500 text-center">
                            {{ $user->email }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            {{ $user->naira_balance }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-500 text-right text-sm">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            <a wire:navigate href="{{ route('admin.users.show', $user) }}" class="flex items-center justify-center text-primary whitespace-nowrap dark:text-slate-300 hover:underline">
                                <i data-lucide="eye" class="w-4 h-4 mr-1"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-20"></i>
                                <p>No platform users found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($paginated)
            <div class="px-5 py-4 border-t border-slate-200/60 dark:border-darkmode-400">
                {{ $this->users->links() }}
            </div>
        @endif
    </div>
</div>
