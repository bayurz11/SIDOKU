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

    public $search = '';
    public $showInactive = false;
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $filterByPermissions = '';

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

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function toggleRoleStatus($roleId)
    {
        $role = Role::findOrFail($roleId);
        $oldStatus = $role->is_active;
        $newStatus = !$role->is_active;
        $status = $oldStatus ? 'deactivated' : 'activated';

        $role->update(['is_active' => $newStatus]);

        // Log the action
        LoggerService::logUserAction(
            'toggle_status',
            'Role',
            $roleId,
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'role_name' => $role->name
            ]
        );

        // Clear related caches
        CacheService::clearRoleCache($roleId);
        CacheService::clearAllUserCaches();
        CacheService::clearDashboardCache();

        // Refresh the component to show updated data
        $this->dispatch('$refresh');

        $this->showSuccessToast("Role {$status} successfully!");
    }

    public function confirmDeleteRole(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        if ($role->name === 'super-admin') {
            $this->showErrorToast('Cannot delete super-admin role.');
            return;
        }

        $this->showConfirm(
            'Delete Role',
            "Are you sure you want to delete role '{$role->display_name}'? This action cannot be undone.",
            'deleteRole',   // panggil method deleteRole
            $roleId,        // kirim langsung ID (sama seperti IPC)
            'Yes, delete it!',
            'Cancel'
        );
    }
    public function deleteRole(int $roleId): void
    {
        dd('DELETE TERPANGGIL', $roleId);
    }



    public function render()
    {
        // Optimize query with proper select and subqueries for counts
        $roles = Role::query()
            ->select([
                'id',
                'name',
                'display_name',
                'description',
                'is_active',
                'created_at',
                'updated_at'
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('display_name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when(!$this->showInactive, function ($query) {
                $query->where('is_active', true);
            })
            ->when($this->filterByPermissions, function ($query) {
                $query->whereHas('permissions', function ($q) {
                    $q->where('group', $this->filterByPermissions);
                });
            })
            ->withCount(['permissions', 'users']) // Use withCount for better performance
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $permissionGroups = ['users', 'roles', 'permissions', 'system'];

        return view('livewire.roles.role-list', compact('roles', 'permissionGroups'));
    }
}
