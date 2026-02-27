<?php

namespace App\Livewire\IncomingMaterial;

use Livewire\Component;
use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;

class IncomingMaterialDetail extends Component
{
    public $showDetail = false;
    public $material = null;
    public $inspectionItems = [];

    protected $listeners = [
        'openIncomingMaterialDetail' => 'loadData'
    ];

    public function loadData($id)
    {
        // Load material beserta files dan inspections
        $this->material = IncomingMaterial::with(['files', 'inspections'])->findOrFail($id);
        $this->showDetail = true;

        // Load inspection items
        $this->inspectionItems = [];

        if ($this->material->inspections && $this->material->inspections->count()) {
            foreach ($this->material->inspections as $inspection) {
                $this->inspectionItems[] = [
                    'parameter'         => $inspection->parameter,
                    'standard'          => $inspection->standard,
                    'test_result'       => $inspection->test_result,
                    'inspection_result' => $inspection->inspection_result,
                ];
            }
        }

        // Kalau tidak ada inspeksi, tambahkan satu baris kosong
        if (empty($this->inspectionItems)) {
            $this->addInspectionItem();
        }
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->material = null;
        $this->inspectionItems = [];
    }

    // Tambah baris inspeksi kosong
    public function addInspectionItem()
    {
        $this->inspectionItems[] = [
            'parameter' => '',
            'standard' => '',
            'test_result' => '',
            'inspection_result' => null,
        ];
    }

    // Hapus baris inspeksi
    public function removeInspectionItem($index)
    {
        unset($this->inspectionItems[$index]);
        $this->inspectionItems = array_values($this->inspectionItems);
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-detail');
    }
}
