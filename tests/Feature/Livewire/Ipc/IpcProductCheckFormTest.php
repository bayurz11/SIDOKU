<?php

namespace Tests\Feature\Livewire\Ipc;

use App\Livewire\Ipc\IpcProductCheckForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IpcProductCheckFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_recalculates_moisture_fields_as_inputs_change(): void
    {
        Livewire::test(IpcProductCheckForm::class)
            ->set('line_group', 'LINE_TEH')
            ->set('cup_weight', 10)
            ->set('product_weight', 5)
            ->assertSet('total_cup_plus_product', 15.0)
            ->assertSet('avg_weight_g', 5.0)
            ->set('weighing_1', 14.3)
            ->set('weighing_2', 14.1)
            ->assertSet('avg_moisture_percent', 16.0);
    }
}
