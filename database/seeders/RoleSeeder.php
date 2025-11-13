<?php

namespace Database\Seeders;

use App\Domains\Role\Models\Role;
use App\Domains\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin role with all permissions
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Has access to all system functions',
                'is_active' => true,
            ]
        );
        
        // Assign all permissions to super admin
        $allPermissions = Permission::all();
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));
        
        // Create Admin role
        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Administrative access to most system functions',
                'is_active' => true,
            ]
        );
        
        // Assign admin permissions (all except system settings)
        $adminPermissions = Permission::whereNotIn('name', ['system.settings'])->get();
        $admin->permissions()->sync($adminPermissions->pluck('id'));
        
        // Create Manager role
        $manager = Role::firstOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Manager',
                'description' => 'Can manage users and view reports',
                'is_active' => true,
            ]
        );
        
        // Assign manager permissions
        $managerPermissions = Permission::whereIn('name', [
            'users.view', 'users.create', 'users.edit',
            'roles.view', 'permissions.view'
        ])->get();
        $manager->permissions()->sync($managerPermissions->pluck('id'));
        
        // Create User role
        $user = Role::firstOrCreate(
            ['name' => 'user'],
            [
                'display_name' => 'User',
                'description' => 'Basic user access',
                'is_active' => true,
            ]
        );
        
        // Users get no additional permissions by default
    }
}
