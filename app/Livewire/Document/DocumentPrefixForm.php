<?php

namespace App\Livewire\Document;

use Livewire\Component;
use App\Shared\Traits\WithAlerts;
use App\Domains\Document\Models\DocumentPrefixSetting;
use App\Domains\Document\Models\DocumentType;
use App\Domains\Department\Models\Department;
use App\Shared\Services\CacheService;
use App\Shared\Services\LoggerService;

class DocumentPrefixForm extends Component
{
    use WithAlerts;

    // ID prefix setting (primary key)
    public ?int $prefixId = null;

    // Form fields
    public string $company_prefix = 'PRP';
    public ?int $document_type_id = null;
    public ?int $department_id = null;
    public ?string $sub_reference_format = null;
    public string $format_nomor = '{{COMP}}/{{MAIN}}/{{DEPT}}/{{SEQ}}';
    public int $reset_interval = 1;
    public ?string $example_output = null;
    public bool $is_active = true;

    // UI state
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?string $editorId = null;

    // Lookup data
    public $documentTypes = [];
    public $departments   = [];

    protected $listeners = [
        'openDocumentPrefixForm' => 'openForm',
    ];

    protected function rules(): array
    {
        return [
            'company_prefix'       => 'required|string|max:20',
            'document_type_id'     => 'nullable|exists:document_types,id',
            'department_id'        => 'nullable|exists:departments,id',
            'sub_reference_format' => 'nullable|string|max:150',
            'format_nomor'         => 'required|string|max:200',
            'reset_interval'       => 'integer|in:0,1,2',
            'example_output'       => 'nullable|string|max:200',
            'is_active'            => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->loadLookups();
    }

    protected function loadLookups(): void
    {
        $this->documentTypes = DocumentType::query()
            ->orderBy('name')
            ->get();

        $this->departments = Department::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Buka modal form
     *
     * Bisa dipanggil:
     *  - $dispatch('openDocumentPrefixForm')
     *  - $dispatch('openDocumentPrefixForm', 1)
     *  - $dispatch('openDocumentPrefixForm', { id: 1 })
     */
    public function openForm($payload = null): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->editorId = 'editor-' . uniqid();
        $this->loadLookups();

        $this->showModal = true;
        $this->isEditing = false;
        $this->is_active = true;

        // Normalisasi ID
        $id = null;
        if (is_array($payload)) {
            $id = $payload['id'] ?? null;
        } elseif (is_numeric($payload)) {
            $id = (int) $payload;
        }

        if ($id) {
            $prefix = DocumentPrefixSetting::findOrFail($id);

            $this->prefixId            = $prefix->id;
            $this->company_prefix      = $prefix->company_prefix ?? 'PRP';
            $this->document_type_id    = $prefix->document_type_id;
            $this->department_id       = $prefix->department_id;
            $this->sub_reference_format = $prefix->sub_reference_format;
            $this->format_nomor        = $prefix->format_nomor;
            $this->reset_interval      = (int) $prefix->reset_interval;
            $this->example_output      = $prefix->example_output;
            $this->is_active           = (bool) $prefix->is_active;

            $this->isEditing = true;
        } else {
            // mode create
            $this->reset([
                'prefixId',
                'document_type_id',
                'department_id',
                'sub_reference_format',
                'example_output',
                'isEditing',
            ]);

            $this->company_prefix = 'PRP';
            $this->format_nomor   = '{{COMP}}/{{MAIN}}/{{DEPT}}/{{SEQ}}';
            $this->reset_interval = 1;
            $this->is_active      = true;
        }
    }

    /**
     * Simpan pengaturan prefix
     */
    public function save(): void
    {
        $this->validate();

        $data = [
            'company_prefix'       => $this->company_prefix,
            'document_type_id'     => $this->document_type_id,
            'department_id'        => $this->department_id,
            'sub_reference_format' => $this->sub_reference_format,
            'format_nomor'         => $this->format_nomor,
            'reset_interval'       => $this->reset_interval,
            'example_output'       => $this->example_output,
            'is_active'            => $this->is_active,
        ];

        if ($this->isEditing && $this->prefixId) {
            $prefix = DocumentPrefixSetting::findOrFail($this->prefixId);
            $prefix->update($data);

            LoggerService::logUserAction('update', 'DocumentPrefixSetting', $prefix->id, [
                'updated_prefix' => $this->company_prefix,
                'format_nomor'   => $this->format_nomor,
            ]);
        } else {
            $prefix = DocumentPrefixSetting::create($data);
            $this->prefixId = $prefix->id;

            LoggerService::logUserAction('create', 'DocumentPrefixSetting', $prefix->id, [
                'created_prefix' => $this->company_prefix,
                'format_nomor'   => $this->format_nomor,
            ]);
        }

        if (class_exists(CacheService::class)) {
            CacheService::clearDashboardCache();
        }

        $this->showSuccessToast('Document prefix setting saved successfully!');
        $this->dispatch('documentPrefix:saved');
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'prefixId',
            'company_prefix',
            'document_type_id',
            'department_id',
            'sub_reference_format',
            'format_nomor',
            'reset_interval',
            'example_output',
            'is_active',
            'isEditing',
            'showModal',
            'editorId',
        ]);

        $this->company_prefix = 'PRP';
        $this->format_nomor   = '{{COMP}}/{{MAIN}}/{{DEPT}}/{{SEQ}}';
        $this->reset_interval = 1;
        $this->is_active      = true;
        $this->showModal      = false;
    }

    public function render()
    {
        return view('livewire.document.document-prefix-form', [
            'documentTypes' => $this->documentTypes,
            'departments'   => $this->departments,
        ]);
    }
}
