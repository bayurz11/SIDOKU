<?php

namespace App\Livewire\Document;

use App\Domains\Department\Models\Department;
use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentType;
use App\Domains\Document\Services\DocumentApprovalService;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentList extends Component
{
    use WithAlerts, WithPagination;

    /** 🔒 SIMPAN USER SEKALI */
    public $authUser;

    public string $search = '';

    public ?int $filterDocumentType = null;

    public ?int $filterDepartment = null;

    public ?string $filterStatus = null;

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public $documentTypes = [];

    public $departments = [];

    public array $statuses = [
        'draft' => 'Draft',
        'in_review' => 'In Review',
        'approved' => 'Approved',
        'obsolete' => 'Obsolete',
    ];

    protected array $allowedSorts = [
        'document_code',
        'level',
        'title',
        'department_id',
        'document_type_id',
        'status',
        'effective_date',
        'created_at',
    ];

    protected array $allowedPerPage = [10, 25, 50, 100];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterDocumentType' => ['except' => null],
        'filterDepartment' => ['except' => null],
        'filterStatus' => ['except' => null],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'document:saved' => 'refreshList',
        'document:imported' => 'refreshList',
    ];

    public function mount(): void
    {
        $this->loadLookups();

        /** 🔑 AMBIL USER SEKALI */
        $this->authUser = auth()->user();

        // 🔒 user basic → kunci department
        if (
            $this->authUser &&
            $this->userHasBasicRole($this->authUser) &&
            $this->authUser->department_id
        ) {
            $this->filterDepartment = $this->authUser->department_id;
        }
    }

    protected function loadLookups(): void
    {
        $this->documentTypes = DocumentType::orderBy('name')->get();
        $this->departments = Department::orderBy('name')->get();
    }

    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDocumentType()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
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
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function markObsolete(int $id): void
    {
        $doc = Document::findOrFail($id);
        $doc->update([
            'status' => Document::STATUS_OBSOLETE ?? 'obsolete',
            'is_active' => false,
        ]);

        $this->showSuccessToast('Document marked as obsolete!');
    }

    public function delete(int $id): void
    {
        Document::findOrFail($id)->delete();
        $this->showSuccessToast('Document deleted!');
        $this->resetPage();
    }

    public function requestApproval(int $id): void
    {
        if (! auth()->user()?->hasAnyPermission(['documents.create', 'documents.review', 'documents.approve'])) {
            $this->showErrorToast('Tidak punya izin mengajukan dokumen.');

            return;
        }

        $doc = Document::findOrFail($id);

        try {
            app(DocumentApprovalService::class)->submit($doc);
            $this->showSuccessToast('Dokumen berhasil diajukan untuk approval.');
            $this->dispatch('document:saved');
        } catch (\Throwable $exception) {
            $this->showErrorToast($exception->getMessage());
        }
    }

    /** ✅ TANPA QUERY DB */
    protected function userHasBasicRole($user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('user');
    }

    /** 🔒 PAKAI USER YANG SUDAH DISIMPAN */
    protected function applyDepartmentScope($query)
    {
        $user = $this->authUser;

        if (! $user || ! $this->userHasBasicRole($user)) {
            return $query;
        }

        if (! $user->department_id) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }

    public function showDetail(int $id): void
    {
        $this->dispatch('openDocumentDetail', id: $id);
    }

    public function render()
    {
        if (! in_array($this->sortField, $this->allowedSorts, true)) {
            $this->sortField = 'created_at';
        }

        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'desc';
        }

        if (! in_array((int) $this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }

        $query = Document::query()
            ->with(['documentType', 'department'])
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('document_code', 'like', $term)
                        ->orWhere('title', 'like', $term)
                        ->orWhere('summary', 'like', $term);
                });
            })
            ->when($this->filterDocumentType, fn ($q) => $q->where('document_type_id', $this->filterDocumentType))
            ->when($this->filterDepartment, fn ($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy($this->sortField, $this->sortDirection);

        $query = $this->applyDepartmentScope($query);

        $data = $query->paginate($this->perPage)->onEachSide(0);

        return view('livewire.document.document-list', compact('data'));
    }
}
