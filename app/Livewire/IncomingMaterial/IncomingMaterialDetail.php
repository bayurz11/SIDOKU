<?php

namespace App\Livewire\IncomingMaterial;

use Livewire\Component;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;

class IncomingMaterialDetail extends Component
{
    public $showDetail = false;
    public $material = null;

    protected $listeners = [
        'openIncomingMaterialDetail' => 'loadData'
    ];

    public function loadData($id)
    {
        $this->material = IncomingMaterial::with('files')->find($id);
        $this->showDetail = true;
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->material = null;
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-detail');
    }
}
