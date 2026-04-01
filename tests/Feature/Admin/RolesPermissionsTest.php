<?php

use App\Livewire\Admin\RolesPermissionsManagement;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $this->superAdmin = User::factory()->create();
    $this->superAdminRole = Role::create(['name' => 'super-admin']);
    $this->superAdmin->assignRole($this->superAdminRole);
});

it('renders the roles and permissions management page for super admins', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.roles.index'))
        ->assertStatus(200);
});

it('can create a role', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(RolesPermissionsManagement::class)
        ->set('roleName', 'Manager')
        ->call('createRole')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('roles', [
        'name' => 'Manager',
    ]);
});

it('can create a permission', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(RolesPermissionsManagement::class)
        ->set('permissionName', 'edit articles')
        ->call('createPermission')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('permissions', [
        'name' => 'edit articles',
    ]);
});

it('can assign permissions to a role', function () {
    $role = Role::create(['name' => 'Editor']);
    $permission = Permission::create(['name' => 'edit articles']);

    Livewire::actingAs($this->superAdmin)
        ->test(RolesPermissionsManagement::class)
        ->call('editRolePermissions', $role->id)
        ->set('selectedPermissions', [$permission->name])
        ->call('saveRolePermissions');

    expect($role->fresh()->hasPermissionTo('edit articles'))->toBeTrue();
});

it('can assign a role to a user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Moderator']);

    Livewire::actingAs($this->superAdmin)
        ->test(RolesPermissionsManagement::class)
        ->call('selectUser', $user->id)
        ->set('userRoles', [$role->name])
        ->call('saveUserRoles');

    expect($user->fresh()->hasRole('Moderator'))->toBeTrue();
});
