<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use App\Domains\Ipc\Models\IpcProduct;

class InProcesControlelDetail extends Component
{
    public bool $showModal = false;
    public ?IpcProduct $ipc = null;

    protected $listeners = [
        // contoh trigger dari luar:
        // $dispatch('openIpcProductDetail', { id: $record->id })
        'openIpcProductDetail' => 'open',
    ];

    public function open(int $id): void
    {
        $this->ipc = IpcProduct::findOrFail($id);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->reset(['showModal', 'ipc']);
    }

    public function render()
    {
        return view('livewire.ipc.in-proces-controlel-detail');
    }
}
