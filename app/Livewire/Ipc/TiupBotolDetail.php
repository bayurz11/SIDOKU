<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use App\Domains\Ipc\Models\TiupBotolCheck;

class TiupBotolDetail extends Component
{
    public bool $showModal = false;
    public ?TiupBotolCheck $record = null;

    protected $listeners = [
        'openTiupBotolDetail' => 'open',
    ];

    public function open(int $id): void
    {
        $this->record    = TiupBotolCheck::findOrFail($id);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->reset(['showModal', 'record']);
    }

    public function render()
    {
        return view('livewire.ipc.tiup-botol-detail');
    }
}
