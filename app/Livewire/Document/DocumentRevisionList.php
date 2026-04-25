<?php

namespace App\Livewire\Document;

use App\Domains\Department\Models\Department;
use App\Domains\Document\Models\DocumentRevision;
use App\Domains\Document\Models\DocumentType;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentRevisionList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $filterDocumentType = null;

    public ?int $filterDepartment = null;

    public int $perPage = 10;

    public string $sortField = 'changed_at';

    public string $sortDirection = 'desc';

    public $documentTypes = [];

    public $departments = [];

    protected array $allowedSorts = [
        'revision_no',
        'changed_at',
        'document_code',
        'title',
        'department_id',
        'document_type_id',
    ];

    protected array $allowedPerPage = [10, 25, 50, 100];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterDocumentType' => ['except' => null],
        'filterDepartment' => ['except' => null],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'changed_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'document:saved' => 'refreshList',
        'document:imported' => 'refreshList',
        'document:approval_updated' => 'refreshList',
    ];

    public function mount(): void
    {
        $this->documentTypes = DocumentType::query()->orderBy('name')->get();
        $this->departments = Department::query()->orderBy('name')->get();
    }

    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDocumentType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        if (! in_array((int) $this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSorts, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    public function render()
    {
        if (! in_array($this->sortField, $this->allowedSorts, true)) {
            $this->sortField = 'changed_at';
        }

        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'desc';
        }

        if (! in_array((int) $this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }

        $query = DocumentRevision::query()
            ->with(['document.documentType', 'document.department', 'changedBy'])
            ->join('documents', 'documents.id', '=', 'document_revisions.document_id')
            ->select('document_revisions.*')
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';

                $q->where(function ($sub) use ($term) {
                    $sub->where('documents.document_code', 'like', $term)
                        ->orWhere('documents.title', 'like', $term)
                        ->orWhere('document_revisions.change_note', 'like', $term);
                });
            })
            ->when($this->filterDocumentType, fn ($q) => $q->where('documents.document_type_id', $this->filterDocumentType))
            ->when($this->filterDepartment, fn ($q) => $q->where('documents.department_id', $this->filterDepartment));

        if ($this->sortField === 'document_code') {
            $query->orderBy('documents.document_code', $this->sortDirection);
        } elseif ($this->sortField === 'title') {
            $query->orderBy('documents.title', $this->sortDirection);
        } elseif ($this->sortField === 'department_id') {
            $query->orderBy('documents.department_id', $this->sortDirection);
        } elseif ($this->sortField === 'document_type_id') {
            $query->orderBy('documents.document_type_id', $this->sortDirection);
        } else {
            $query->orderBy('document_revisions.'.$this->sortField, $this->sortDirection);
        }

        $data = $query->paginate($this->perPage)->onEachSide(0);

        return view('livewire.document.document-revision-list', compact('data'));
    }
}
