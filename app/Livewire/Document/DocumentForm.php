<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Shared\Traits\WithAlerts;
use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentType;
use App\Domains\Department\Models\Department;
use App\Domains\Document\Services\DocumentNumberService;
use App\Shared\Services\LoggerService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // <- TAMBAHAN
use Illuminate\Validation\Rule;

class DocumentForm extends Component
{
    use WithFileUploads, WithAlerts;

    // Primary key
    public ?int $documentId = null;

    // Form fields
    public ?int $document_type_id = null;
    public ?int $department_id = null;
    public ?int $parent_document_id = null;

    public string $title = '';
    public ?string $summary = null;

    public ?string $effective_date = null;
    public ?string $expired_date = null;

    // 1 = Manual / DOC level 1, 2 = SOP, 3 = WI, 4 = FORM, dll
    public int $level = 1;
    public string $status = 'draft';  // draft, in_review, approved, obsolete
    public bool $is_active = true;

    // File upload
    public $uploaded_file;
    public ?string $existing_file_path = null;

    // Read-only display
    public ?string $document_code = null;

    // UI state
    public bool $showModal = false;
    public bool $isEditing = false;

    // Dropdown data
    public $documentTypes = [];
    public $departments   = [];
    public $parentDocuments = [];

    protected array $statusOptions = [
        'draft',
        'in_review',
        'approved',
        'obsolete',
    ];

    protected $listeners = [
        'openDocumentForm' => 'openForm',
    ];

    protected function rules(): array
    {
        return [
            'document_type_id'   => ['required', 'exists:document_types,id'],
            'department_id'      => ['nullable', 'exists:departments,id'],
            'parent_document_id' => ['nullable', 'exists:documents,id'],
            'title'              => ['required', 'string', 'max:255'],
            'summary'            => ['nullable', 'string'],
            'effective_date'     => ['nullable', 'date'],
            'expired_date'       => ['nullable', 'date', 'after_or_equal:effective_date'],
            'level'              => ['integer', 'min:1', 'max:10'],
            'status'             => ['required', Rule::in($this->statusOptions)],
            'is_active'          => ['boolean'],
            'uploaded_file'      => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:5120'],
        ];
    }

    public function mount(): void
    {
        $this->loadLookups();
        $this->status = 'draft';
        $this->level = 1;
    }

    protected function loadLookups(): void
    {
        $this->documentTypes = DocumentType::query()
            ->orderBy('name')
            ->get();

        $this->departments = Department::query()
            ->orderBy('name')
            ->get();

        // Parent documents (hanya approved & active)
        $this->parentDocuments = Document::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->orderBy('document_code')
            ->limit(100)
            ->get();
    }

    /**
     * Buka modal form
     * Bisa dipanggil:
     *  - $dispatch('openDocumentForm')
     *  - $dispatch('openDocumentForm', 1)
     *  - $dispatch('openDocumentForm', { id: 1 })
     */
    public function openForm(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->loadLookups();

        // default mode: create
        $this->showModal         = true;
        $this->isEditing         = false;
        $this->uploaded_file     = null;
        $this->document_code     = null;
        $this->existing_file_path = null;

        // default field
        $this->status    = 'draft';
        $this->level     = 1;
        $this->is_active = true;

        if ($id) {
            // MODE EDIT
            $doc = Document::findOrFail($id);

            $this->documentId          = $doc->id;
            $this->document_type_id    = $doc->document_type_id;
            $this->department_id       = $doc->department_id;
            $this->parent_document_id  = $doc->parent_document_id;

            $this->title               = $doc->title;
            $this->summary             = $doc->summary;

            $this->effective_date      = optional($doc->effective_date)?->format('Y-m-d');
            $this->expired_date        = optional($doc->expired_date)?->format('Y-m-d');

            $this->level               = (int) $doc->level;
            $this->status              = $doc->status;
            $this->is_active           = (bool) $doc->is_active;

            $this->document_code       = $doc->document_code;
            $this->existing_file_path  = $doc->file_path;

            $this->isEditing           = true;
        } else {
            // MODE CREATE → reset field, biarkan default di atas
            $this->reset([
                'documentId',
                'document_type_id',
                'department_id',
                'parent_document_id',
                'title',
                'summary',
                'effective_date',
                'expired_date',
                'document_code',
                'existing_file_path',
                'uploaded_file',
            ]);
        }
    }


    /**
     * Helper upload file ke disk 'public_path' (public/storage)
     * dan hapus file lama kalau ada.
     */
    protected function handleFileUpload(?string $oldPath = null): ?string
    {
        // kalau tidak ada file baru, kembalikan path lama
        if (!$this->uploaded_file) {
            return $oldPath;
        }

        // pastikan folder "documents" ada
        Storage::disk('public_path')->makeDirectory('documents');

        // ambil ekstensi (default pdf kalau tidak ada)
        $ext = strtolower($this->uploaded_file->getClientOriginalExtension() ?: 'pdf');

        // nama file random
        $fileName = Str::random(24) . '.' . $ext;

        // simpan ke public/storage/documents/xxx.ext → path relatif: "documents/xxx.ext"
        $relativePath = $this->uploaded_file->storeAs('documents', $fileName, 'public_path');

        // hapus file lama kalau ada
        if ($oldPath) {
            $old = ltrim($oldPath, '/');
            if (Storage::disk('public_path')->exists($old)) {
                Storage::disk('public_path')->delete($old);
            }
        }

        return $relativePath;
    }

    /**
     * Simpan dokumen (create / update)
     */
    public function save(): void
    {
        $this->validate();

        $parent = $this->parent_document_id
            ? Document::find($this->parent_document_id)
            : null;

        if ($this->isEditing && $this->documentId) {
            // UPDATE existing document
            $document = Document::findOrFail($this->documentId);

            // upload file baru + hapus lama (kalau ada)
            $filePath = $this->handleFileUpload($document->file_path);

            $document->update([
                'document_type_id'   => $this->document_type_id,
                'department_id'      => $this->department_id,
                'parent_document_id' => $this->parent_document_id,
                'title'              => $this->title,
                'summary'            => $this->summary,
                'effective_date'     => $this->effective_date,
                'expired_date'       => $this->expired_date,
                'level'              => $this->level,
                'status'             => $this->status,
                'file_path'          => $filePath,
                'is_active'          => $this->is_active,
                'updated_by'         => auth()->id(),
            ]);

            LoggerService::logUserAction('update', 'Document', $document->id, [
                'document_code' => $document->document_code,
                'title'         => $document->title,
            ]);

            $this->showSuccessToast('Document updated successfully!');
        } else {
            // CREATE new document → generate nomor
            $generated = DocumentNumberService::generate(
                $this->document_type_id,
                $this->department_id,
                $parent
            );

            // upload file baru (tidak ada file lama)
            $filePath = $this->handleFileUpload(null);

            $document = Document::create([
                'document_type_id'            => $this->document_type_id,
                'department_id'               => $this->department_id,
                'document_prefix_setting_id'  => $generated['prefix_setting_id'] ?? null,
                'parent_document_id'          => $this->parent_document_id,
                'document_code'               => $generated['code'],
                'title'                       => $this->title,
                'summary'                     => $this->summary,
                'effective_date'              => $this->effective_date,
                'expired_date'                => $this->expired_date,
                'level'                       => $this->level,
                'revision_no'                 => 0,
                'status'                      => $this->status ?: 'draft',
                'file_path'                   => $filePath,
                'is_active'                   => $this->is_active,
                'created_by'                  => auth()->id(),
            ]);

            LoggerService::logUserAction('create', 'Document', $document->id, [
                'document_code' => $document->document_code,
                'title'         => $document->title,
            ]);

            $this->showSuccessToast('Document created successfully!');
        }

        $this->dispatch('document:saved');
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'documentId',
            'document_type_id',
            'department_id',
            'parent_document_id',
            'title',
            'summary',
            'effective_date',
            'expired_date',
            'level',
            'status',
            'is_active',
            'uploaded_file',
            'existing_file_path',
            'document_code',
            'showModal',
            'isEditing',
        ]);

        $this->status    = 'draft';
        $this->level     = 1;
        $this->is_active = true;
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.document.document-form', [
            'documentTypes'   => $this->documentTypes,
            'departments'     => $this->departments,
            'parentDocuments' => $this->parentDocuments,
        ]);
    }
}
