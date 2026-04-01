<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'manage dashboard',
            'manage wallets',
            'manage p2p',
            'manage kyc',
            'manage settings',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }

        // create roles and assign created permissions
        $role = \Spatie\Permission\Models\Role::create(['name' => 'Super Admin']);
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        // Assign to first user
        $user = \App\Models\User::first();
        if ($user) {
            $user->assignRole($role);
        }
    }
}
