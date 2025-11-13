<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithPagination;
use App\Shared\Traits\WithAlerts;
use App\Domains\Document\Models\DocumentType;
use App\Shared\Services\CacheService;
use App\Shared\Services\LoggerService;

class DocumentTypeList extends Component
{
    use WithPagination, WithAlerts;

    /** Filter & State */
    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    /** Allowed fields for sorting and pagination */
    protected array $allowedSorts = ['name', 'description', 'created_at', 'is_active'];
    protected array $allowedPerPage = [10, 25, 50];

    /** Query string sync */
    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    /** Listen to refresh event from form */
    protected $listeners = ['documentTypeUpdated' => '$refresh'];

    /** Reset pagination when filters change */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingShowInactive()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    /** Sorting logic */
    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->allowedSorts)) return;

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /** Toggle active/inactive status */
    public function toggleStatus(int $id): void
    {
        $item = DocumentType::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        LoggerService::logUserAction('toggle_status', 'DocumentType', $id, [
            'new_status' => $item->is_active,
            'name' => $item->name
        ]);

        CacheService::clearDashboardCache();
        $this->showSuccessToast('Status updated!');
    }

    /** Delete document type */
    public function delete(int $id): void
    {
        $item = DocumentType::findOrFail($id);
        $name = $item->name;
        $item->delete();

        LoggerService::logUserAction('delete', 'DocumentType', $id, [
            'deleted_name' => $name
        ], 'warning');

        CacheService::clearDashboardCache();
        $this->showSuccessToast('Document type deleted!');
    }

    /** Render view with data */
    public function render()
    {
        $data = DocumentType::query()
            ->when(
                $this->search,
                fn($q) => $q->where(
                    fn($qq) => $qq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                )
            )
            ->when(!$this->showInactive, fn($q) => $q->where('is_active', true))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.document.document-type-list', compact('data'));
    }
}
