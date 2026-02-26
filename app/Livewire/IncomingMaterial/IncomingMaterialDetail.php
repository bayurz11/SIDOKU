<?php

namespace App\Livewire\IncomingMaterial;

use Livewire\Component;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;

class IncomingMaterialDetail extends Component
{
    public $material;
    public $showModal = false;

    protected $listeners = ['openIncomingMaterialDetail'];

    public function openIncomingMaterialDetail($id)
    {
        $this->material = IncomingMaterial::with('files')->findOrFail($id);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->reset(['material', 'showModal']);
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-detail');
    }
}
