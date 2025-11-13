<?php

namespace App\Livewire\Department;

use Livewire\Component;
use Livewire\WithPagination;
use App\Shared\Traits\WithAlerts;
use App\Domains\Department\Models\Department;
use App\Shared\Services\CacheService;
use App\Shared\Services\LoggerService;

class DepartmentList extends Component
{
    use WithPagination, WithAlerts;

    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    protected array $allowedSorts = ['name', 'description', 'created_at', 'is_active'];
    protected array $allowedPerPage = [10, 25, 50];

    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = ['departmentUpdated' => '$refresh'];

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



    /** Toggle active/inactive */
    public function toggleStatus(int $id): void
    {
        $item = Department::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        LoggerService::logUserAction('toggle_status', 'Department', $id, [
            'new_status' => $item->is_active,
            'name' => $item->name
        ]);

        CacheService::clearDashboardCache();
        $this->showSuccessToast('Status updated!');
    }

    /** Delete department */
    public function delete(int $id): void
    {
        $item = Department::findOrFail($id);
        $name = $item->name;
        $item->delete();

        LoggerService::logUserAction('delete', 'Department', $id, [
            'deleted_name' => $name
        ], 'warning');

        CacheService::clearDashboardCache();
        $this->showSuccessToast('Department deleted!');
    }

    public function render()
    {
        $data = Department::query()
            ->when(
                $this->search,
                fn($q) =>
                $q->where(
                    fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                )
            )
            ->when(!$this->showInactive, fn($q) => $q->where('is_active', true))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.department.department-list', compact('data'));
    }
}
