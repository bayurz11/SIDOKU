<?php

namespace App\Livewire\IncomingMaterial;

use Livewire\Component;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;

class IncomingMaterialDetail extends Component
{
    public $material;
    public $showDetail = false; // ← WAJIB ADA

    protected $listeners = ['showIncomingMaterialDetail'];

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

    public function closeModal()
    {
        $this->reset(['material']);
        $this->showDetail = false;
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-detail');
    }
}
