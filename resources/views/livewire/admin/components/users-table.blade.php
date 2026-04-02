<div class="overflow-x-auto overflow-y-hidden">
    <table class="w-full text-left">
        <thead class="border-b border-slate-200/60 transition-colors dark:border-darkmode-400">
            <tr>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap">Name</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-center">Email</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-center">Roles</th>
                <th class="px-5 py-4 font-medium text-slate-500 whitespace-nowrap text-right">Joined</th>
            </tr>
        </thead>
        <tbody>
            @forelse($this->users as $user)
            <tr class="border-b border-slate-200/60 last:border-0 hover:bg-slate-50 transition-colors dark:border-darkmode-400 dark:hover:bg-darkmode-400/30">
                <td class="px-5 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center mr-3 text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <div class="font-medium text-slate-700 dark:text-slate-300">{{ trim($user->name) }}</div>
                    </div>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-slate-500 text-center">
                    {{ $user->email }}
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                        @forelse($user->roles as $role)
                            <span class="px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-medium uppercase tracking-wider">{{ $role->name }}</span>
                        @empty
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-400 text-[10px] font-medium uppercase tracking-wider">User</span>
                        @endforelse
                    </div>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-slate-500 text-right text-sm">
                    {{ $user->created_at->format('M d, Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-10 text-center text-slate-400">
                    <div class="flex flex-col items-center justify-center">
                        <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-20"></i>
                        <p>No platform users found.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
