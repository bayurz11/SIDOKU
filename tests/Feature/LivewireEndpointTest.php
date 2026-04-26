<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LivewireEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_livewire_update_endpoint_is_registered(): void
    {
        $this->assertTrue(Route::has('livewire.update'));

        $this->postJson('/livewire/update', [
            'components' => [],
        ])->assertOk();
    }
}
