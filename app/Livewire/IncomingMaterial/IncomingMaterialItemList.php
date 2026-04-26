<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialItem;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;
use Livewire\WithPagination;

class IncomingMaterialItemList extends Component
{
    use WithAlerts, WithPagination;

    public string $search = '';

    public bool $showInactive = false;

    public int $perPage = 10;

    protected $listeners = [
        'incoming-material-item:saved' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingShowInactive(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $item = IncomingMaterialItem::findOrFail($id);
        $item->update([
            'is_active' => ! $item->is_active,
            'updated_by' => auth()->id(),
        ]);

        $this->showSuccessToast('Status master barang berhasil diperbarui.');
    }

    public function delete(int $id): void
    {
        $item = IncomingMaterialItem::withCount('incomingMaterials')->findOrFail($id);

        if ($item->incoming_materials_count > 0) {
            $this->showErrorToast('Master barang sudah dipakai di Incoming Material dan tidak bisa dihapus.');

            return;
        }

        $item->delete();
        $this->showSuccessToast('Master barang berhasil dihapus.');
    }

    public function render()
    {
        $data = IncomingMaterialItem::query()
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($searchQuery) use ($term) {
                    $searchQuery->where('name', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhere('default_unit', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when(! $this->showInactive, fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.incoming-material.incoming-material-item-list', compact('data'));
    }
}
