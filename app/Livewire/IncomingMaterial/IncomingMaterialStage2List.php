<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use Livewire\Component;
use Livewire\WithPagination;

class IncomingMaterialStage2List extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'ready';

    public int $perPage = 10;

    protected $listeners = [
        'incoming-material-stage2:saved' => '$refresh',
        'incoming-material-microbiology:saved' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function openForm(int $incomingMaterialId): void
    {
        $this->dispatch('openIncomingMaterialStage2Form', id: $incomingMaterialId);
    }

    public function render()
    {
        $data = IncomingMaterial::query()
            ->with(['item', 'stage2Check', 'microbiologyTest'])
            ->where('status', 'ACCEPTED')
            ->when($this->status === 'ready', fn ($query) => $query->whereDoesntHave('stage2Check'))
            ->when($this->status === 'checked', fn ($query) => $query->whereHas('stage2Check'))
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($searchQuery) use ($term) {
                    $searchQuery->where('material_name', 'like', $term)
                        ->orWhere('supplier', 'like', $term)
                        ->orWhere('batch_number', 'like', $term)
                        ->orWhereHas('item', fn ($itemQuery) => $itemQuery->where('name', 'like', $term));
                });
            })
            ->latest('date')
            ->paginate($this->perPage);

        return view('livewire.incoming-material.incoming-material-stage2-list', compact('data'));
    }
}
