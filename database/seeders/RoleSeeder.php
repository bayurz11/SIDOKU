<?php

namespace Database\Seeders;

use App\Domains\Permission\Models\Permission;
use App\Domains\Role\Models\Role;
use App\Shared\Services\CacheService;
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
            'roles.view', 'permissions.view',
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

        $this->createDocumentWorkflowRole(
            'document-controller',
            'Document Controller',
            'Reviews controlled documents before final QMS approval.',
            [
                'documents.view',
                'documents.review',
                'documents.approve',
                'documents.download',
            ]
        );

        $this->createDocumentWorkflowRole(
            'quality-system-manager',
            'Quality System Manager',
            'Performs final approval and manages approved document revisions.',
            [
                'documents.view',
                'documents.review',
                'documents.approve',
                'documents.revision',
                'documents.download',
            ]
        );
    }

    private function createDocumentWorkflowRole(
        string $name,
        string $displayName,
        string $description,
        array $permissions
    ): void {
        $role = Role::updateOrCreate(
            ['name' => $name],
            [
                'display_name' => $displayName,
                'description' => $description,
                'is_active' => true,
            ]
        );

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->syncWithoutDetaching($permissionIds);
        CacheService::clearRoleCache($role->id);
    }
}
