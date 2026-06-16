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
                            <i class="bx bx-shield mr-2 text-base"></i>
                            Roles
                        </button>
                    </li>
                    <li class="flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem]" role="presentation">
                        <button type="button" wire:click="selectTab('permissions')" 
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] px-3 py-1.5 transition-colors {{ $activeTab === 'permissions' ? 'active bg-white font-medium text-slate-700 shadow-sm' : 'text-slate-500' }}" 
                                role="tab">
                            <i class="bx bx-key mr-2 text-base"></i>
                            Permissions
                        </button>
                    </li>
                    <li class="flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem]" role="presentation">
                        <button type="button" wire:click="selectTab('users')" 
                                class="flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] px-3 py-1.5 transition-colors {{ $activeTab === 'users' ? 'active bg-white font-medium text-slate-700 shadow-sm' : 'text-slate-500' }}" 
                                role="tab">
                            <i class="bx bx-users mr-2 text-base"></i>
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
                                                                <i class="bx bx-edit-alt text-base"></i>
                                                            </button>
                                                            @if($role->name !== 'super-admin')
                                                                <button wire:click="deleteRole({{ $role->id }})" class="text-danger hover:text-danger/70"
                                                                        onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                                                    <i class="bx bx-trash text-base"></i>
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

                        <!-- Role Detail Panel: Permissions & Linked Users -->
                        @if($selectedRole)
                            <div class="col-span-12">
                                <div class="box box--stacked p-5 mt-5">
                                    <div class="flex items-center justify-between mb-5 border-b border-dashed border-slate-200 pb-5">
                                        <div>
                                            <div class="text-lg font-semibold text-slate-700">Manage Role: <span class="text-primary">{{ $selectedRole->name }}</span></div>
                                            <div class="text-xs text-slate-500 mt-1">Configure permissions and view users linked to this role.</div>
                                        </div>
                                        <button wire:click="$set('selectedRole', null)" class="text-slate-400 hover:text-slate-600 transition-colors">
                                            <i class="bx bx-x text-lg"></i>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-12 gap-6">
                                        <!-- Left Column: Permissions -->
                                        <div class="col-span-12 lg:col-span-7 border-r border-dashed border-slate-200 pr-0 lg:pr-6">
                                            <div class="text-sm font-medium text-slate-600 mb-4 flex items-center">
                                                <i class="bx bx-shield-quarter mr-2 text-primary text-base"></i>
                                                Role Permissions
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[400px] overflow-y-auto pr-2">
                                                @foreach($this->permissions as $permission)
                                                    <div class="flex items-center bg-slate-50 hover:bg-slate-100/80 p-3 rounded-lg border border-slate-100 transition-all">
                                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" 
                                                               id="perm-{{ $permission->id }}"
                                                               class="w-4 h-4 border-slate-300 rounded text-primary focus:ring-primary cursor-pointer">
                                                        <label for="perm-{{ $permission->id }}" class="ml-2.5 text-sm text-slate-600 font-medium cursor-pointer truncate" title="{{ $permission->name }}">
                                                            {{ $permission->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-dashed border-slate-100">
                                                <button wire:click="$set('selectedRole', null)" class="px-4 py-2 border border-slate-200 rounded-md text-slate-500 hover:bg-slate-50 transition-all text-sm font-medium">Cancel</button>
                                                <button wire:click="saveRolePermissions" class="bg-primary hover:bg-primary/95 text-white px-5 py-2 rounded-md shadow-sm font-medium transition-all text-sm">Save Permissions</button>
                                            </div>
                                        </div>

                                        <!-- Right Column: Linked Users -->
                                        <div class="col-span-12 lg:col-span-5">
                                            <div class="text-sm font-medium text-slate-600 mb-4 flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <i class="bx bx-users mr-2 text-primary text-base"></i>
                                                    Linked Users
                                                </div>
                                                <span class="bg-slate-100 text-slate-600 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                    {{ count($this->selectedRoleUsers) }}
                                                </span>
                                            </div>
                                            
                                            <div class="max-h-[400px] overflow-y-auto pr-2 space-y-3">
                                                @forelse($this->selectedRoleUsers as $user)
                                                    <div class="flex items-center justify-between p-3 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-all">
                                                        <div class="flex items-center min-w-0 mr-3">
                                                            <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm border border-primary/20 shrink-0">
                                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                                            </div>
                                                            <div class="ml-3 min-w-0">
                                                                <div class="text-sm font-medium text-slate-700 truncate">{{ $user->name }}</div>
                                                                <div class="text-xs text-slate-500 truncate">{{ $user->email }}</div>
                                                            </div>
                                                        </div>
                                                        <button wire:click="removeUserFromRole({{ $user->id }})" 
                                                                onclick="confirm('Are you sure you want to remove this role from {{ addslashes($user->name) }}?') || event.stopImmediatePropagation()"
                                                                class="text-danger hover:bg-danger/10 p-1.5 rounded-md transition-all shrink-0" 
                                                                title="Remove role from user">
                                                            <i class="bx bx-user-minus text-base"></i>
                                                        </button>
                                                    </div>
                                                @empty
                                                    <div class="flex flex-col items-center justify-center py-12 text-center bg-slate-50/50 rounded-lg border border-dashed border-slate-200">
                                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                                                            <i class="bx bx-user-x text-2xl text-slate-400"></i>
                                                        </div>
                                                        <div class="text-sm font-medium text-slate-600">No linked users</div>
                                                        <div class="text-xs text-slate-400 mt-1">No users are currently assigned to this role.</div>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if($activeTab === 'permissions')
                    <div class="grid grid-cols-12 gap-6" wire:key="tab-content-permissions">
                        <!-- Create Permission -->
                        <div class="col-span-12 lg:col-span-4">
                            <div class="box box--stacked p-5 h-fit">
                                <div class="text-base font-medium mb-5">Create New Permission</div>
                                <form wire:submit.prevent="createPermission">
                                    <div>
                                        <label class="form-label text-slate-500 text-xs uppercase mb-2 block">Permission Name</label>
                                        <input wire:model="permissionName" type="text" 
                                               class="w-full border-slate-200 rounded-md focus:ring-primary focus:border-primary px-3 py-2 text-sm shadow-sm" 
                                               placeholder="e.g. manage reports">
                                        @error('permissionName') <span class="text-danger text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md shadow-sm mt-5 w-full font-medium transition-all hover:bg-primary/95">
                                        Create Permission
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Permissions List -->
                        <div class="col-span-12 lg:col-span-8">
                            <div class="box box--stacked p-5">
                                <div class="text-base font-medium mb-5">Existing Permissions</div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="border-b border-dashed border-slate-300/80">
                                                <th class="px-5 py-3 font-medium text-slate-500 whitespace-nowrap">Name</th>
                                                <th class="px-5 py-3 font-medium text-slate-500 whitespace-nowrap text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($this->permissions as $permission)
                                                <tr class="border-b border-dashed border-slate-300/80 last:border-0 hover:bg-slate-50/50">
                                                    <td class="px-5 py-4 font-medium">{{ $permission->name }}</td>
                                                    <td class="px-5 py-4">
                                                        <div class="flex items-center justify-center gap-3">
                                                            <button wire:click="deletePermission({{ $permission->id }})" class="text-danger hover:text-danger/70"
                                                                    onclick="confirm('Are you sure you want to delete this permission? This cannot be undone.') || event.stopImmediatePropagation()">
                                                                <i class="bx bx-trash text-base"></i>
                                                            </button>
                                                        </div>
                                                    </td>
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
                                    <i class="bx bx-search absolute left-3 top-2.5 text-lg text-slate-400"></i>
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
                                            <i class="bx bx-x text-lg"></i>
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
