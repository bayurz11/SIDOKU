<?php

namespace App\Livewire\IncomingMaterial;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialItem;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;

class IncomingMaterialItemForm extends Component
{
    use WithAlerts;

    public ?int $itemId = null;

    public string $name = '';

    public string $category = IncomingMaterialItem::CATEGORY_GENERAL;

    public ?string $default_unit = null;

    public bool $requires_microbiology = false;

    public string $stage2_fields_text = '';

    public bool $is_active = true;

    public ?string $description = null;

    public bool $showModal = false;

    public bool $isEditing = false;

    protected $listeners = [
        'openIncomingMaterialItemForm' => 'openForm',
    ];

    public function openForm(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $item = IncomingMaterialItem::findOrFail($id);

            $this->itemId = $item->id;
            $this->name = $item->name;
            $this->category = $item->category;
            $this->default_unit = $item->default_unit;
            $this->requires_microbiology = (bool) $item->requires_microbiology;
            $this->stage2_fields_text = implode("\n", $item->stage2FieldList());
            $this->is_active = (bool) $item->is_active;
            $this->description = $item->description;
            $this->isEditing = true;
        }

        $this->showModal = true;
    }

    public function updatedCategory(string $category): void
    {
        if ($category === IncomingMaterialItem::CATEGORY_TEA) {
            $this->requires_microbiology = true;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:incoming_material_items,name,'.($this->itemId ?: 'NULL').',id'],
            'category' => ['required', 'string', 'max:50'],
            'default_unit' => ['nullable', 'string', 'max:30'],
            'requires_microbiology' => ['boolean'],
            'stage2_fields_text' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $fields = collect(preg_split('/\r\n|\r|\n/', $this->stage2_fields_text))
            ->map(fn ($field) => trim((string) $field))
            ->filter()
            ->values()
            ->all();

        $data = [
            'name' => $this->name,
            'category' => $this->category,
            'default_unit' => $this->default_unit,
            'requires_microbiology' => $this->requires_microbiology,
            'stage2_fields' => $fields,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'updated_by' => auth()->id(),
        ];

        if ($this->itemId) {
            IncomingMaterialItem::findOrFail($this->itemId)->update($data);
        } else {
            $data['created_by'] = auth()->id();
            IncomingMaterialItem::create($data);
        }

        $this->showSuccessToast('Master nama barang berhasil disimpan.');
        $this->dispatch('incoming-material-item:saved');
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'itemId',
            'name',
            'default_unit',
            'requires_microbiology',
            'stage2_fields_text',
            'description',
            'isEditing',
        ]);

        $this->category = IncomingMaterialItem::CATEGORY_GENERAL;
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.incoming-material.incoming-material-item-form');
    }
}
