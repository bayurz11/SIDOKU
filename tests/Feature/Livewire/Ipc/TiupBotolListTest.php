<?php

namespace Tests\Feature\Livewire\Ipc;

use App\Domains\Ipc\Models\TiupBotolCheck;
use App\Livewire\Ipc\TiupBotolList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Livewire\Ipc\Concerns\BuildsIpcUsers;
use Tests\TestCase;

class TiupBotolListTest extends TestCase
{
    use BuildsIpcUsers;
    use RefreshDatabase;

    public function test_it_shows_tiup_botol_summary_cards(): void
    {
        $user = $this->createUserWithPermissions(['ipc_tiup_botol.view']);

        $this->actingAs($user);

        TiupBotolCheck::query()->create([
            'tanggal' => '2026-04-20',
            'nama_botol' => 'Botol A',
            'drop_test' => 'TDK_BCR',
            'penyebaran_rata' => 'OK',
            'bottom_tidak_menonjol' => 'OK',
            'tidak_ada_material' => 'OK',
        ]);

        TiupBotolCheck::query()->create([
            'tanggal' => '2026-04-21',
            'nama_botol' => 'Botol B',
            'drop_test' => 'BCR',
            'penyebaran_rata' => 'NOK',
            'bottom_tidak_menonjol' => 'OK',
            'tidak_ada_material' => 'OK',
        ]);

        Livewire::test(TiupBotolList::class)
            ->assertSee('Tiup Botol Overview')
            ->assertSee('Total Sampel')
            ->assertSee('Drop Test OK')
            ->assertSee('Drop Test NG')
            ->assertSee('Visual NOK')
            ->assertSee('2')
            ->assertSee('1');
    }
}
