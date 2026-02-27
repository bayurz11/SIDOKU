<?php

namespace App\Livewire\IncomingMaterial;

use Livewire\Component;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;

class IncomingMaterialList extends Component
{
    public $material = null;
    public bool $showDetail = false;

    protected $listeners = [
        'incoming-material:saved' => '$refresh',
    ];

    public function showIncomingMaterialDetail($id)
    {
        $this->material = IncomingMaterial::with('files')->find($id);

        if (!$this->material) {
            return;
        }

        $this->showDetail = true;
    }

    public function closeDetail()
    {
        $this->reset(['showDetail', 'material']);
    }

    public function render()
    {
        $materials = IncomingMaterial::latest()->get();

        return view('livewire.incoming-material.incoming-material-list', [
            'materials' => $materials
        ]);
    }
}
