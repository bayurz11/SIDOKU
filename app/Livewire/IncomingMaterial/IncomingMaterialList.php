<?php

namespace App\Livewire\IncomingMaterial;

use Livewire\Component;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;

class IncomingMaterialList extends Component
{
    public function showIncomingMaterialDetail($id)
    {
        $this->dispatch('openIncomingMaterialDetail', id: $id);
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-list', [
            'incomingData' => IncomingMaterial::latest('date')->get()
        ]);
    }
}
