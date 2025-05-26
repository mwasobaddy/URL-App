<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Plan management
            'view plans',
            'create plans',
            'edit plans',
            'delete plans',
            
            // Subscription management
            'view subscriptions',
            'manage subscriptions',
            'view revenue',
            
            // User management
            'view users',
            'manage users',
            
            // URL List permissions (already existing)
            'create lists',
            'edit lists',
            'delete lists',
            'share lists',
            'view lists',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'free' => [
                'create lists',
                'edit lists',
                'delete lists',
                'share lists',
                'view lists',
            ],
            'premium' => [
                'create lists',
                'edit lists',
                'delete lists',
                'share lists',
                'view lists',
            ],
            'admin' => $permissions, // Admin gets all permissions
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create(['name' => $roleName]);
            $role->givePermissionTo($rolePermissions);
        }

        // Create admin user
        $admin = User::create([
            'name' => 'Kelvin Mwangi Wanjohi',
            'email' => 'kelvinramsiel@gmail.com',
            'password' => bcrypt('Mwas@1234'),
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('admin');
    }
}
