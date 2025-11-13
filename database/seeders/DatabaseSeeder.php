<?php

namespace Database\Seeders;

use App\Domains\User\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
        
        // Create a super admin user manually
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        // Assign super admin role
        $superAdminRole = \App\Domains\Role\Models\Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }
        
        // Create a test user manually
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        // Assign user role
        $userRole = \App\Domains\Role\Models\Role::where('name', 'user')->first();
        if ($userRole) {
            $testUser->roles()->attach($userRole->id);
        }
    }
}
