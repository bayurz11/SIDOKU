<?php

namespace Tests\Feature\Livewire\Ipc;

use App\Domains\Ipc\Models\IpcProductCheck;
use App\Livewire\Ipc\IpcProductCheckList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Livewire\Ipc\Concerns\BuildsIpcUsers;
use Tests\TestCase;

class IpcProductCheckListTest extends TestCase
{
    use BuildsIpcUsers;
    use RefreshDatabase;

    public function test_it_filters_ipc_product_checks_and_can_reset_filters(): void
    {
        $user = $this->createUserWithPermissions(['ipc_moisture.view']);

        $this->actingAs($user);

        IpcProductCheck::query()->create([
            'line_group' => 'LINE_TEH',
            'sub_line' => 'TEH_ORI',
            'test_date' => '2026-04-10',
            'product_name' => 'Teh Original',
            'shift' => 1,
            'avg_moisture_percent' => 6.25,
            'avg_weight_g' => 12.5,
        ]);

        IpcProductCheck::query()->create([
            'line_group' => 'LINE_TEH',
            'sub_line' => 'TEH_SACHET',
            'test_date' => '2026-04-11',
            'product_name' => 'Teh Sachet',
            'shift' => 2,
            'avg_moisture_percent' => 7.1,
            'avg_weight_g' => 10.1,
        ]);

        IpcProductCheck::query()->create([
            'line_group' => 'LINE_POWDER',
            'sub_line' => null,
            'test_date' => '2026-04-12',
            'product_name' => 'Powder Mix',
            'shift' => 3,
            'avg_moisture_percent' => 4.8,
            'avg_weight_g' => 9.3,
        ]);

        Livewire::test(IpcProductCheckList::class)
            ->set('filterLineGroup', 'LINE_TEH')
            ->assertSee('Teh Original')
            ->assertSee('Teh Sachet')
            ->assertDontSee('Powder Mix')
            ->set('search', 'Sachet')
            ->assertDontSee('Teh Original')
            ->assertSee('Teh Sachet')
            ->call('resetFilters')
            ->assertSet('filterLineGroup', null)
            ->assertSet('search', '')
            ->assertSee('Powder Mix');
    }
}
