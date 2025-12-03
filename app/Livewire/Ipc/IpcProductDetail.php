<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use App\Domains\Ipc\Models\IpcProductCheck;

class IpcProductDetail extends Component
{
    public bool $showModal = false;
    public ?IpcProductCheck $ipc = null;

    protected $listeners = [
        // trigger dari luar: $dispatch('openIpcProductDetail', id: $record->id)
        'openIpcProductDetail' => 'open',
    ];

    public function open(int $id): void
    {
        $this->ipc = IpcProductCheck::findOrFail($id);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->reset(['showModal', 'ipc']);
    }

    public function render()
    {
        return view('livewire.ipc.ipc-product-detail');
    }
}
