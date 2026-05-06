<div class="container">
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 mt-3.5">
            <div class="flex h-10 items-center">
                <div class="text-base font-medium">
                    Roles & Permissions Management
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="mt-3.5">
                <ul class="flex overflow-hidden rounded-[0.6rem] border border-slate-200 bg-white p-0.5 shadow-sm" role="tablist">
                    <li class="flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem]" role="presentation">
                        <button type="button" wire:click="selectTab('roles')" 
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] px-3 py-1.5 transition-colors {{ $activeTab === 'roles' ? 'active bg-white font-medium text-slate-700 shadow-sm' : 'text-slate-500' }}" 
                                role="tab">
                            <i data-lucide="shield" class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                            Roles
                        </button>
                    </li>
                    <li class="flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem]" role="presentation">
                        <button type="button" wire:click="selectTab('permissions')" 
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] px-3 py-1.5 transition-colors {{ $activeTab === 'permissions' ? 'active bg-white font-medium text-slate-700 shadow-sm' : 'text-slate-500' }}" 
                                role="tab">
                            <i data-lucide="key" class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                            Permissions
                        </button>
                    </li>
                    <li class="flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem]" role="presentation">
                        <button type="button" wire:click="selectTab('users')" 
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] px-3 py-1.5 transition-colors {{ $activeTab === 'users' ? 'active bg-white font-medium text-slate-700 shadow-sm' : 'text-slate-500' }}" 
                                role="tab">
                            <i data-lucide="users" class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                            User Assignments
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="mt-6">
                @if($activeTab === 'roles')
                    <div class="grid grid-cols-12 gap-6" wire:key="tab-content-roles">
                        <!-- Create Role -->
                        <div class="col-span-12 lg:col-span-4">
                            <div class="box box--stacked p-5 h-fit">
                                <div class="text-base font-medium mb-5">Create New Role</div>
                                <form wire:submit.prevent="createRole">
                                    <div>
                                        <label class="form-label text-slate-500 text-xs uppercase mb-2 block">Role Name</label>
                                        <input wire:model="roleName" type="text" 
                                               class="w-full border-slate-200 rounded-md focus:ring-primary focus:border-primary px-3 py-2 text-sm shadow-sm" 
                                               placeholder="e.g. Manager">
                                        @error('roleName') <span class="text-danger text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md shadow-sm mt-5 w-full font-medium">
                                        Create Role
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Roles List -->
                        <div class="col-span-12 lg:col-span-8">
                            <div class="box box--stacked p-5">
                                <div class="text-base font-medium mb-5">Existing Roles</div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="border-b border-dashed border-slate-300/80">
                                                <th class="px-5 py-3 font-medium text-slate-500 whitespace-nowrap">Name</th>
                                                <th class="px-5 py-3 font-medium text-slate-500 whitespace-nowrap text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($this->roles as $role)
                                                <tr class="border-b border-dashed border-slate-300/80 last:border-0 hover:bg-slate-50/50">
                                                    <td class="px-5 py-4 font-medium">{{ $role->name }}</td>
                                                    <td class="px-5 py-4">
                                                        <div class="flex items-center justify-center gap-3">
                                                            <button wire:click="editRolePermissions({{ $role->id }})" class="text-primary hover:text-primary/70">
                                                                <i data-lucide="edit-3" class="h-4 w-4"></i>
                                                            </button>
                                                            @if($role->name !== 'super-admin')
                                                                <button wire:click="deleteRole({{ $role->id }})" class="text-danger hover:text-danger/70"
                                                                        onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Role Permissions Modal/Panel (Inline for simplicity) -->
                        @if($selectedRole)
                            <div class="col-span-12">
                                <div class="box box--stacked p-5 mt-5">
                                    <div class="flex items-center justify-between mb-5">
                                        <div class="text-base font-medium">Edit Permissions for: <span class="text-primary">{{ $selectedRole->name }}</span></div>
                                        <button wire:click="$set('selectedRole', null)" class="text-slate-500">
                                            <i data-lucide="x" class="h-5 w-5"></i>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        @foreach($this->permissions as $permission)
                                            <div class="flex items-center bg-slate-50/80 p-3 rounded-lg border border-dashed border-slate-200">
                                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" 
                                                       class="w-4 h-4 border-slate-300 rounded text-primary focus:ring-primary">
                                                <label class="ml-2 text-sm text-slate-600 truncate" title="{{ $permission->name }}">{{ $permission->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-8 flex justify-end gap-3 border-t border-dashed pt-5 cursor-pointer">
                                        <button wire:click="$set('selectedRole', null)" class="px-4 py-2 border rounded-md text-slate-500">Cancel</button>
                                        <button wire:click="saveRolePermissions" class="bg-primary text-white px-6 py-2 rounded-md shadow-sm font-medium">Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if($activeTab === 'permissions')
                    <div class="grid grid-cols-12 gap-6" wire:key="tab-content-permissions">


                        <!-- Permissions List -->
                        <div class="col-span-12">
                            <div class="box box--stacked p-5">
                                <div class="text-base font-medium mb-5">Existing Permissions</div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="border-b border-dashed border-slate-300/80">
                                                <th class="px-5 py-3 font-medium text-slate-500 whitespace-nowrap">Name</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($this->permissions as $permission)
                                                <tr class="border-b border-dashed border-slate-300/80 last:border-0 hover:bg-slate-50/50">
                                                    <td class="px-5 py-4 font-medium">{{ $permission->name }}</td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($activeTab === 'users')
                    <div class="grid grid-cols-12 gap-6" wire:key="tab-content-users">
                        <!-- User Search -->
                        <div class="col-span-12">
                            <div class="box box--stacked p-5">
                                <div class="text-base font-medium mb-5">Assign Roles to Users</div>
                                <div class="relative max-w-md">
                                    <i data-lucide="search" class="absolute left-3 top-3 h-4 w-4 text-slate-400"></i>
                                    <input wire:model.live="searchUser" type="text" 
                                           class="w-full border-slate-200 rounded-md focus:ring-primary focus:border-primary pl-10 pr-3 py-2 text-sm shadow-sm" 
                                           placeholder="Search by name or email...">
                                </div>

                                @if(strlen($searchUser) >= 2)
                                    <div class="mt-5 border-t border-dashed pt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @forelse($this->users as $user)
                                            <div wire:click="selectUser({{ $user->id }})" 
                                                 class="flex items-center p-3 rounded-lg border border-dashed border-slate-300 cursor-pointer hover:bg-slate-50 shadow-sm transition-all {{ $selectedUser?->id === $user->id ? 'bg-primary/5 border-primary' : 'bg-white' }}">
                                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mr-3 border border-slate-200">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                                <div class="truncate">
                                                    <div class="font-medium text-sm">{{ $user->name }}</div>
                                                    <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-span-full py-5 text-center text-slate-500">No users found.</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- User Assignment Panel -->
                        @if($selectedUser)
                            <div class="col-span-12">
                                <div class="box box--stacked p-5 h-fit mt-5">
                                    <div class="flex items-center justify-between mb-5">
                                        <div class="text-base font-medium">Assign Roles to: <span class="text-primary">{{ $selectedUser->name }}</span></div>
                                        <button wire:click="$set('selectedUser', null)" class="text-slate-500">
                                            <i data-lucide="x" class="h-5 w-5"></i>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        @foreach($this->roles as $role)
                                            <div class="flex items-center bg-slate-50/80 p-3 rounded-lg border border-dashed border-slate-200">
                                                <input type="checkbox" wire:model="userRoles" value="{{ $role->name }}" 
                                                       class="w-4 h-4 border-slate-300 rounded text-primary focus:ring-primary">
                                                <label class="ml-2 text-sm text-slate-600 truncate" title="{{ $role->name }}">{{ $role->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-8 flex justify-end gap-3 border-t border-dashed pt-5">
                                        <button wire:click="$set('selectedUser', null)" class="px-4 py-2 border rounded-md text-slate-500">Cancel</button>
                                        <button wire:click="saveUserRoles" class="bg-primary text-white px-6 py-2 rounded-md shadow-sm font-medium">Save Roles</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        lucide.createIcons();
        
        Livewire.hook('morph.updated', ({ el, component }) => {
            lucide.createIcons();
        });
    });
</script>
