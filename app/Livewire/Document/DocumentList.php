<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithPagination;
use App\Shared\Traits\WithAlerts;
use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentType;
use App\Domains\Department\Models\Department;

class DocumentList extends Component
{
    use WithPagination, WithAlerts;

    public string $search = '';
    public ?int $filterDocumentType = null;
    public ?int $filterDepartment = null;
    public ?string $filterStatus = null;

    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Untuk dropdown
    public $documentTypes = [];
    public $departments   = [];
    public array $statuses = [
        'draft'     => 'Draft',
        'in_review' => 'In Review',
        'approved'  => 'Approved',
        'obsolete'  => 'Obsolete',
    ];

    protected array $allowedSorts = [
        'document_code',
        'title',
        'department_id',
        'document_type_id',
        'status',
        'effective_date',
        'created_at',
    ];

    protected array $allowedPerPage = [10, 25, 50, 100];

    protected $queryString = [
        'search'             => ['except' => ''],
        'filterDocumentType' => ['except' => null],
        'filterDepartment'   => ['except' => null],
        'filterStatus'       => ['except' => null],
        'perPage'            => ['except' => 10],
        'sortField'          => ['except' => 'created_at'],
        'sortDirection'      => ['except' => 'desc'],
    ];

    protected $listeners = [
        'document:saved'    => 'refreshList',   // dari DocumentForm
        'document:imported' => 'refreshList',   // dari fitur Import Daftar Induk Dokumen (Excel)
    ];

    public function mount(): void
    {
        $this->loadLookups();

        // 🔒 Kalau user biasa (role "user"), kunci filterDepartment ke department dia
        $user = auth()->user();
        if ($user && $this->userHasBasicRole($user) && $user->department_id) {
            $this->filterDepartment = $user->department_id;
        }
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
        if (!in_array($this->perPage, $this->allowedPerPage)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->allowedSorts)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleActive(int $id): void
    {
        $doc = Document::findOrFail($id);
        $doc->update(['is_active' => !$doc->is_active]);

        $this->showSuccessToast('Document status updated!');
    }

    public function markObsolete(int $id): void
    {
        $doc = Document::findOrFail($id);
        $doc->update([
            'status'    => Document::STATUS_OBSOLETE ?? 'obsolete',
            'is_active' => false,
        ]);

        $this->showSuccessToast('Document marked as obsolete!');
    }

    public function delete(int $id): void
    {
        $doc = Document::findOrFail($id);
        $doc->delete();

        $this->showSuccessToast('Document deleted!');
        $this->resetPage();
    }

    /**
     * Cek apakah user adalah role "user" (basic user)
     */
    protected function userHasBasicRole($user): bool
    {
        // Kalau pakai Spatie / Laratrust yang punya hasRole()
        if (method_exists($user, 'hasRole') && $user->hasRole('user')) {
            return true;
        }

        // Fallback: cek via relasi roles
        if (method_exists($user, 'roles')) {
            return $user->roles()
                ->where('name', 'user')
                ->exists();
        }

        return false;
    }
    public function showDetail(int $id): void
    {
        // Dispatch event Livewire ke component lain (DocumentDetailForm)
        $this->dispatch('openDocumentDetail', id: $id);
    }

    /**
     * Terapkan pembatasan department untuk role "user"
     */
    protected function applyDepartmentScope($query)
    {
        $user = auth()->user();

        if (!$user || !$this->userHasBasicRole($user)) {
            // Bukan user biasa, biarkan lihat semua dokumen (tetap bisa difilter manual)
            return $query;
        }

        // Kalau user tidak punya department_id, jangan batasi (atau bisa juga return whereNull kalau mau)
        if (!$user->department_id) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }

    public function render()
    {
        $query = Document::query()
            ->with(['documentType', 'department'])
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('document_code', 'like', $term)
                        ->orWhere('title', 'like', $term)
                        ->orWhere('summary', 'like', $term);
                });
            })
            ->when($this->filterDocumentType, fn($q) => $q->where('document_type_id', $this->filterDocumentType))
            ->when($this->filterDepartment, fn($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy($this->sortField, $this->sortDirection);

        // 🔒 Terapkan pembatasan department untuk role "user"
        $query = $this->applyDepartmentScope($query);

        $data = $query->paginate($this->perPage);

        return view('livewire.document.document-list', compact('data'));
    }
}
