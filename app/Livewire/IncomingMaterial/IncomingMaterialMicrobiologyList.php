<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;
use Livewire\Component;
use Livewire\WithPagination;

class IncomingMaterialMicrobiologyList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'pending';

    public int $perPage = 10;

    protected $listeners = [
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
        $this->dispatch('openIncomingMaterialMicrobiologyForm', id: $incomingMaterialId);
    }

    public function render()
    {
        $data = IncomingMaterial::query()
            ->with(['item', 'microbiologyTest'])
            ->where(function ($query) {
                $query->where('test_microbiology', true)
                    ->orWhereHas('item', fn ($itemQuery) => $itemQuery->where('requires_microbiology', true));
            })
            ->when($this->status === 'pending', fn ($query) => $query->whereDoesntHave('microbiologyTest', fn ($microQuery) => $microQuery->where('status', 'COMPLETED')))
            ->when($this->status === 'completed', fn ($query) => $query->whereHas('microbiologyTest', fn ($microQuery) => $microQuery->where('status', 'COMPLETED')))
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

        return view('livewire.incoming-material.incoming-material-microbiology-list', compact('data'));
    }
}
