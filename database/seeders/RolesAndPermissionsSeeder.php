<?php

namespace Database\Seeders;

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
            'revert transactions',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // create roles
        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin']);

        // As an industry standard, we can also create standard roles like admin or user here:
        // \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        // Assign super-admin to first user if they exist
        $user = \App\Models\User::first();
        if ($user && ! $user->hasRole('super-admin')) {
            $user->assignRole($superAdminRole);
        }
    }
}
