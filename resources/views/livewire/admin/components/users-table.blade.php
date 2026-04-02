<div class="overflow-x-auto">
    <table class="w-full text-left">
        <thead class="border-b border-slate-200/60 font-medium text-slate-500 dark:border-darkmode-400">
            <tr>
                <th class="px-5 py-3 border-b-2 dark:border-darkmode-400 whitespace-nowrap">Name</th>
                <th class="px-5 py-3 border-b-2 dark:border-darkmode-400 whitespace-nowrap">Email</th>
                <th class="px-5 py-3 border-b-2 dark:border-darkmode-400 whitespace-nowrap">Roles</th>
                <th class="px-5 py-3 border-b-2 dark:border-darkmode-400 whitespace-nowrap">Joined</th>
            </tr>
        </thead>
        <tbody>
            @forelse($this->users as $user)
            <tr class="border-b border-slate-200/60 last:border-0 dark:border-darkmode-400">
                <td class="px-5 py-3 whitespace-nowrap">
                    <div class="font-medium truncate">{{ trim($user->name) }}</div>
                </td>
                <td class="px-5 py-3 whitespace-nowrap text-slate-500">
                    {{ $user->email }}
                </td>
                <td class="px-5 py-3 whitespace-nowrap">
                    @foreach($user->roles as $role)
                        <span class="px-2 py-0.5 rounded bg-primary/10 text-primary text-xs mr-1">{{ $role->name }}</span>
                    @endforeach
                    @if($user->roles->isEmpty())
                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-xs">User</span>
                    @endif
                </td>
                <td class="px-5 py-3 whitespace-nowrap text-slate-500">
                    {{ $user->created_at->format('M d, Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-5 text-center text-slate-500">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
