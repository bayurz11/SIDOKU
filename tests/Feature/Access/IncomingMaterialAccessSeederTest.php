<?php

namespace Tests\Feature\Access;

use App\Domains\Permission\Models\Permission;
use App\Domains\Role\Models\Role;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomingMaterialAccessSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_incoming_material_and_microbiology_permissions_are_seeded_for_roles(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertDatabaseHas('permissions', [
            'name' => 'incoming_material.view',
            'group' => 'incoming_material',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'microbiology.view',
            'group' => 'microbiology',
            'is_active' => true,
        ]);

        $qcIncoming = Role::query()
            ->where('name', 'qc-incoming')
            ->firstOrFail();
        $microbiologyAnalyst = Role::query()
            ->where('name', 'microbiology-analyst')
            ->firstOrFail();

        $this->assertTrue($qcIncoming->permissions()->where('permissions.name', 'incoming_material.view')->exists());
        $this->assertTrue($qcIncoming->permissions()->where('permissions.name', 'microbiology.view')->exists());
        $this->assertTrue($microbiologyAnalyst->permissions()->where('permissions.name', 'microbiology.edit')->exists());

        $this->assertNotEmpty(Permission::query()->where('group', 'microbiology')->pluck('name'));
    }
}
