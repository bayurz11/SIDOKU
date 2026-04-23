<?php

namespace Tests\Feature\Livewire\Ipc;

use App\Domains\Ipc\Models\TiupBotolCheck;
use App\Domains\User\Models\User;
use App\Livewire\Ipc\TiupBotolForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Livewire\Ipc\Concerns\BuildsIpcUsers;
use Tests\TestCase;

class TiupBotolFormTest extends TestCase
{
    use BuildsIpcUsers;
    use RefreshDatabase;

    public function test_editing_tiup_botol_keeps_created_by_and_updates_updated_by(): void
    {
        $creator = User::factory()->create();
        $editor = $this->createUserWithPermissions(['ipc_tiup_botol.edit']);

        $record = TiupBotolCheck::query()->create([
            'tanggal' => '2026-04-15',
            'nama_botol' => 'Botol 600ml',
            'drop_test' => 'TDK_BCR',
            'penyebaran_rata' => 'OK',
            'bottom_tidak_menonjol' => 'OK',
            'tidak_ada_material' => 'OK',
            'catatan' => 'Initial',
            'created_by' => $creator->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(TiupBotolForm::class)
            ->call('open', $record->id)
            ->set('catatan', 'Updated by editor')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('tiup_botol_checks', [
            'id' => $record->id,
            'created_by' => $creator->id,
            'updated_by' => $editor->id,
            'catatan' => 'Updated by editor',
        ]);
    }
}
