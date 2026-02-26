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
        try {
            $this->material = IncomingMaterial::with('files')->find($id); // gunakan find() bukan findOrFail

            if (! $this->material) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'title' => 'Data tidak ditemukan!'
                ]);
                return;
            }

            $this->showDetail = true;
        } catch (\Throwable $e) {
            report($e); // log error ke laravel.log
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Terjadi error saat menampilkan detail!'
            ]);
        }
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
