<?php

namespace App\Livewire\Roles;

use App\Domains\Role\Models\Role;
use App\Shared\Services\CacheService;
use App\Shared\Services\LoggerService;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;
use Livewire\WithPagination;

class RoleList extends Component
{
    use WithPagination, WithAlerts;

    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 10;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public string $filterByPermissions = '';

    protected array $allowedSorts = ['name', 'display_name', 'description', 'is_active', 'created_at', 'updated_at'];
    protected array $allowedPerPage = [10, 25, 50, 100];

    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
        'filterByPermissions' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterByPermissions()
    {
        $this->resetPage();
    }

    public function updatingShowInactive()
    {
        $this->resetPage();
    }

    public function updatingPerPage($value)
    {
        $this->perPage = in_array((int) $value, $this->allowedPerPage, true) ? (int) $value : 10;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if (! in_array($field, $this->allowedSorts, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function toggleRoleStatus($roleId)
    {
        $role = Role::findOrFail($roleId);
        $oldStatus = $role->is_active;
        $newStatus = ! $role->is_active;
        $status = $oldStatus ? 'deactivated' : 'activated';

        $role->update(['is_active' => $newStatus]);

        LoggerService::logUserAction(
            'toggle_status',
            'Role',
            $roleId,
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'role_name' => $role->name,
            ]
        );

        CacheService::clearRoleCache($roleId);
        CacheService::clearDashboardCache();

        $this->dispatch('$refresh');
        $this->showSuccessToast("Role {$status} successfully!");
    }

    public function deleteRole(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        if ($role->name === 'super-admin') {
            $this->showErrorToast('Cannot delete super-admin role.');

            return;
        }

        LoggerService::logUserAction(
            'delete',
            'Role',
            $roleId,
            [
                'deleted_role_name' => $role->name,
                'deleted_role_display_name' => $role->display_name,
                'had_permissions' => $role->permissions->pluck('name')->toArray(),
            ],
            'warning'
        );

        CacheService::clearRoleCache($roleId);
        CacheService::clearDashboardCache();

        $role->delete();

        $this->showSuccessToast('Role deleted successfully!');
        $this->resetPage();
        $this->dispatch('roleSaved');
    }

    public function render()
    {
        if (! in_array($this->sortField, $this->allowedSorts, true)) {
            $this->sortField = 'name';
        }

        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'asc';
        }

        $roles = Role::query()
            ->select([
                'id',
                'name',
                'display_name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('display_name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when(! $this->showInactive, function ($query) {
                $query->where('is_active', true);
            })
            ->when($this->filterByPermissions, function ($query) {
                $query->whereHas('permissions', function ($q) {
                    $q->where('group', $this->filterByPermissions);
                });
            })
            ->withCount(['permissions', 'users'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
            ->onEachSide(0);

        $permissionGroups = ['users', 'roles', 'permissions', 'system'];

        return view('livewire.roles.role-list', compact('roles', 'permissionGroups'));
    }
}
