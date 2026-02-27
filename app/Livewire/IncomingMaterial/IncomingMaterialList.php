<?php

namespace App\Livewire\IncomingMaterial;

use Livewire\Component;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;

class IncomingMaterialList extends Component
{
    public $material;
    public bool $showDetail = false;

    public function showIncomingMaterialDetail($id)
    {
        $this->material = IncomingMaterial::with('files')->find($id);

        if (! $this->material) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Data tidak ditemukan!',
            ]);
            return;
        }

        $this->showDetail = true;
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->material = null;
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-list');
    }
}
