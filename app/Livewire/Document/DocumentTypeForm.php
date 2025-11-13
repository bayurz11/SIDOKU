<?php

namespace App\Livewire\Document;

use Livewire\Component;
use App\Shared\Traits\WithAlerts;
use App\Shared\Services\CacheService;
use App\Shared\Services\LoggerService;
// use App\Domains\Document\Models\DocumenType; // <- typo, tidak dipakai
use App\Domains\Document\Models\DocumentType;

class DocumentTypeForm extends Component
{
    use WithAlerts;

    /** @var int|null */
    public ?int $documentTypeId = null;

    /** Form fields */
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    /** UI State */
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?string $editorId = null;

    /** Event listener */
    protected $listeners = [
        'openDocumentTypeForm' => 'openForm',
    ];

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:255|unique:document_types,name,' . ($this->documentTypeId ?: 'NULL') . ',id',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ];
    }

    /**
     * Buka modal form
     */
    public function openForm(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->editorId = 'editor-' . uniqid();

        // default mode: create
        $this->showModal = true;
        $this->isEditing = false;
        $this->is_active = true;

        if ($id) {
            $type = DocumentType::findOrFail($id);

            $this->documentTypeId = $type->id;
            $this->name           = $type->name;
            $this->description    = $type->description ?? '';
            $this->is_active      = (bool) $type->is_active;
            $this->isEditing      = true;
        } else {
            // reset field data, biarkan is_active tetap true
            $this->reset(['documentTypeId', 'name', 'description', 'isEditing']);
        }
    }

    /**
     * Simpan data jenis dokumen
     */
    public function save(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'description' => $this->description,
            'is_active'   => $this->is_active,
        ];

        if ($this->isEditing && $this->documentTypeId) {
            DocumentType::findOrFail($this->documentTypeId)->update($data);

            LoggerService::logUserAction('update', 'DocumentType', $this->documentTypeId, [
                'updated_name' => $this->name,
            ]);
        } else {
            $type = DocumentType::create($data);

            LoggerService::logUserAction('create', 'DocumentType', $type->id, [
                'created_name' => $this->name,
            ]);

            // kalau mau lanjut edit setelah create
            $this->documentTypeId = $type->id;
        }

        CacheService::clearDashboardCache();

        $this->showSuccessToast('Document type saved successfully!');
        $this->dispatch('documentType:saved'); // sesuaikan listener di list
        $this->closeModal();
    }

    /**
     * Tutup modal & reset state
     */
    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'documentTypeId',
            'name',
            'description',
            'is_active',
            'isEditing',
            'showModal',
            'editorId',
        ]);

        // default state
        $this->is_active = true;
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.document.document-type-form');
    }
}
