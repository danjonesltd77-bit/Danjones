<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsManagement extends Component
{
    public $activeTab = 'roles';

    // Role state
    public $roleName = '';
    public $selectedRole;
    public $selectedPermissions = [];

    // Permission state
    public $permissionName = '';

    // User Assignment state
    public $searchUser = '';
    public $selectedUser;
    public $userRoles = [];

    public function mount()
    {
        //
    }

    #[Computed]
    public function roles()
    {
        return Role::all();
    }

    #[Computed]
    public function permissions()
    {
        return Permission::all();
    }

    #[Computed]
    public function users()
    {
        if (strlen($this->searchUser) < 2) {
            return [];
        }

        return User::where('name', 'like', '%' . $this->searchUser . '%')
            ->orWhere('email', 'like', '%' . $this->searchUser . '%')
            ->limit(10)
            ->get();
    }

    public function selectTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Role methods
    public function createRole()
    {
        $this->validate([
            'roleName' => 'required|min:3|unique:roles,name',
        ]);

        Role::create(['name' => $this->roleName]);
        $this->roleName = '';
    }

    public function editRolePermissions($roleId)
    {
        $this->selectedRole = Role::findById($roleId);
        $this->selectedPermissions = $this->selectedRole->permissions->pluck('name')->toArray();
    }

    public function saveRolePermissions()
    {
        if ($this->selectedRole) {
            $this->selectedRole->syncPermissions($this->selectedPermissions);
            $this->selectedRole = null;
            $this->selectedPermissions = [];
        }
    }

    public function deleteRole($roleId)
    {
        Role::findById($roleId)->delete();
    }

    // Permission methods
    public function createPermission()
    {
        $this->validate([
            'permissionName' => 'required|min:3|unique:permissions,name',
        ]);

        Permission::create(['name' => $this->permissionName]);
        $this->permissionName = '';
    }

    public function deletePermission($permissionId)
    {
        Permission::findById($permissionId)->delete();
    }

    // User Assignment methods
    public function selectUser($userId)
    {
        $this->selectedUser = User::find($userId);
        if ($this->selectedUser) {
            $this->userRoles = $this->selectedUser->roles->pluck('name')->toArray();
        }
    }

    public function saveUserRoles()
    {
        if ($this->selectedUser) {
            $this->selectedUser->syncRoles($this->userRoles);
            $this->selectedUser = null;
            $this->userRoles = [];
            $this->searchUser = '';
        }
    }

    public function render()
    {
        return view('livewire.admin.roles-permissions-management')
            ->layout('layouts.app');
    }
}
