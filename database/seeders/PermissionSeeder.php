<?php

namespace Database\Seeders;

use App\Domains\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User permissions
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'Can view user list and details', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Can create new users', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'description' => 'Can edit existing users', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Can delete users', 'group' => 'users'],

            // Role permissions
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'description' => 'Can view role list and details', 'group' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Can create new roles', 'group' => 'roles'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Roles', 'description' => 'Can edit existing roles', 'group' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Can delete roles', 'group' => 'roles'],

            // Permission permissions
            ['name' => 'permissions.view', 'display_name' => 'View Permissions', 'description' => 'Can view permission list', 'group' => 'permissions'],
            ['name' => 'permissions.manage', 'display_name' => 'Manage Permissions', 'description' => 'Can assign/remove permissions from roles', 'group' => 'permissions'],

            // System permissions
            ['name' => 'system.settings', 'display_name' => 'System Settings', 'description' => 'Can access system settings', 'group' => 'system'],
            ['name' => 'system.logs', 'display_name' => 'View Logs', 'description' => 'Can view system logs', 'group' => 'system'],

            // Department permissions 
            ['name' => 'departments.view', 'display_name' => 'View Departments', 'description' => 'Can view department list and details', 'group' => 'departments'],
            ['name' => 'departments.create', 'display_name' => 'Create Departments', 'description' => 'Can create new departments', 'group' => 'departments'],
            ['name' => 'departments.edit', 'display_name' => 'Edit Departments', 'description' => 'Can edit existing departments', 'group' => 'departments'],
            ['name' => 'departments.delete', 'display_name' => 'Delete Departments', 'description' => 'Can delete departments', 'group' => 'departments'],

            // Document Categories permissions
            ['name' => 'document_categories.view', 'display_name' => 'View Document Categories', 'description' => 'Can view document category list and details', 'group' => 'document_categories'],
            ['name' => 'document_categories.create', 'display_name' => 'Create Document Categories', 'description' => 'Can create new document categories', 'group' => 'document_categories'],
            ['name' => 'document_categories.edit', 'display_name' => 'Edit Document Categories', 'description' => 'Can edit existing document categories', 'group' => 'document_categories'],
            ['name' => 'document_categories.delete', 'display_name' => 'Delete Document Categories', 'description' => 'Can delete document categories', 'group' => 'document_categories'],

            // Document Types permissions
            ['name' => 'document_types.view', 'display_name' => 'View Document Types', 'description' => 'Can view document type list and details', 'group' => 'document_types'],
            ['name' => 'document_types.create', 'display_name' => 'Create Document Types', 'description' => 'Can create new document types', 'group' => 'document_types'],
            ['name' => 'document_types.edit', 'display_name' => 'Edit Document Types', 'description' => 'Can edit existing document types', 'group' => 'document_types'],
            ['name' => 'document_types.delete', 'display_name' => 'Delete Document Types', 'description' => 'Can delete document types', 'group' => 'document_types'],

            // Document Prefix Settings permissions
            ['name' => 'document_prefix_settings.view', 'display_name' => 'View Document Prefix Settings', 'description' => 'Can view the list and details of document prefix configurations', 'group' => 'document_prefix_settings',],
            ['name' => 'document_prefix_settings.create', 'display_name' => 'Create Document Prefix Settings', 'description' => 'Can create new document prefix setting entries', 'group' => 'document_prefix_settings',],
            ['name' => 'document_prefix_settings.edit', 'display_name' => 'Edit Document Prefix Settings', 'description' => 'Can edit or update existing document prefix settings', 'group' => 'document_prefix_settings',],
            ['name' => 'document_prefix_settings.delete', 'display_name' => 'Delete Document Prefix Settings', 'description' => 'Can delete document prefix settings permanently', 'group' => 'document_prefix_settings',],
            ['name' => 'document_prefix_settings.toggle', 'display_name' => 'Activate/Deactivate Prefix Setting', 'description' => 'Can enable or disable prefix settings', 'group' => 'document_prefix_settings',],
            ['name' => 'document_prefix_settings.generate_preview', 'display_name' => 'Generate Number Preview', 'description' => 'Can generate or preview document numbering pattern outputs', 'group' => 'document_prefix_settings',],
        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
