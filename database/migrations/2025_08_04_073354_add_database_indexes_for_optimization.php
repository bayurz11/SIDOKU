<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email', 'is_active']);
            $table->index('is_active');
            $table->index('created_at');
        });

        // Add indexes for roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->index('is_active');
            $table->index(['name', 'is_active']);
            $table->index('created_at');
        });

        // Add indexes for permissions table
        Schema::table('permissions', function (Blueprint $table) {
            $table->index('is_active');
            $table->index(['group', 'is_active']);
            $table->index(['name', 'is_active']);
            $table->index('created_at');
        });

        // Add indexes for pivot tables for better relationship queries
        Schema::table('user_roles', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('role_id');
            $table->index(['user_id', 'role_id']);
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->index('role_id');
            $table->index('permission_id');
            $table->index(['role_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email', 'is_active']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes for roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['name', 'is_active']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes for permissions table
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['group', 'is_active']);
            $table->dropIndex(['name', 'is_active']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes for pivot tables
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['role_id']);
            $table->dropIndex(['user_id', 'role_id']);
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropIndex(['role_id']);
            $table->dropIndex(['permission_id']);
            $table->dropIndex(['role_id', 'permission_id']);
        });
    }
};
