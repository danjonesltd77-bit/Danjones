<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesPermissionsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create an admin with super-admin role
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');

        // Create a regular user
        $this->regularUser = User::factory()->create();
    }

    public function test_non_admin_cannot_access_roles_permissions_page(): void
    {
        $this->actingAs($this->regularUser);

        Livewire::test(\App\Livewire\Admin\Roles\RolesPermissionsManagement::class)
            ->assertForbidden();
    }

    public function test_admin_can_access_roles_permissions_page(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Admin\Roles\RolesPermissionsManagement::class)
            ->assertOk();
    }

    public function test_admin_can_create_and_delete_role(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(\App\Livewire\Admin\Roles\RolesPermissionsManagement::class)
            ->set('roleName', 'Test Role')
            ->call('createRole')
            ->assertHasNoErrors()
            ->assertSet('roleName', '');

        $this->assertDatabaseHas('roles', ['name' => 'Test Role']);

        $role = Role::findByName('Test Role');

        $component->call('deleteRole', $role->id);

        $this->assertDatabaseMissing('roles', ['name' => 'Test Role']);
    }

    public function test_admin_can_create_and_delete_permission(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(\App\Livewire\Admin\Roles\RolesPermissionsManagement::class)
            ->set('permissionName', 'test permission')
            ->call('createPermission')
            ->assertHasNoErrors()
            ->assertSet('permissionName', '');

        $this->assertDatabaseHas('permissions', ['name' => 'test permission']);

        $permission = Permission::findByName('test permission');

        $component->call('deletePermission', $permission->id);

        $this->assertDatabaseMissing('permissions', ['name' => 'test permission']);
    }

    public function test_admin_can_sync_role_permissions(): void
    {
        $this->actingAs($this->admin);

        $role = Role::firstOrCreate(['name' => 'Support']);
        Permission::firstOrCreate(['name' => 'manage dashboard']);
        Permission::firstOrCreate(['name' => 'manage settings']);

        Livewire::test(\App\Livewire\Admin\Roles\RolesPermissionsManagement::class)
            ->call('editRolePermissions', $role->id)
            ->assertSet('selectedRole.id', $role->id)
            ->set('selectedPermissions', ['manage dashboard'])
            ->call('saveRolePermissions')
            ->assertHasNoErrors();

        $this->assertTrue($role->hasPermissionTo('manage dashboard'));
        $this->assertFalse($role->hasPermissionTo('manage settings'));
    }

    public function test_admin_can_view_users_linked_to_role_and_remove_them(): void
    {
        $this->actingAs($this->admin);

        $role = Role::firstOrCreate(['name' => 'Editor']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->assignRole($role);
        $user2->assignRole($role);

        // Test computed selectedRoleUsers shows these users
        Livewire::test(\App\Livewire\Admin\Roles\RolesPermissionsManagement::class)
            ->call('editRolePermissions', $role->id)
            ->assertSet('selectedRole.id', $role->id)
            ->assertSee($user1->name)
            ->assertSee($user2->name)
            ->call('removeUserFromRole', $user1->id);

        $this->assertFalse($user1->fresh()->hasRole($role->name));
        $this->assertTrue($user2->fresh()->hasRole($role->name));
    }
}
