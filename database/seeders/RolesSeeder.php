<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view datasets', 'create datasets', 'edit datasets', 'delete datasets',
            'view crop-cycles', 'create crop-cycles', 'edit crop-cycles', 'delete crop-cycles',
            'view reports', 'generate reports', 'download reports',
            'view analytics',
            'manage users',
            'view activity-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions); // Admin gets all

        $researcher = Role::firstOrCreate(['name' => 'researcher']);
        $researcher->syncPermissions([
            'view datasets', 'create datasets', 'edit datasets',
            'view crop-cycles', 'create crop-cycles', 'edit crop-cycles',
            'view reports', 'generate reports', 'download reports',
            'view analytics',
        ]);

        $farmer = Role::firstOrCreate(['name' => 'farmer']);
        $farmer->syncPermissions([
            'view datasets', 'create datasets',
            'view crop-cycles', 'create crop-cycles',
            'view reports', 'download reports',
        ]);
    }
}
