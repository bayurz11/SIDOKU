<?php

namespace App\Livewire\Users;

use App\Domains\User\Models\User;
use App\Shared\Services\CacheService;
use App\Shared\Services\LoggerService;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination, WithAlerts;

    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 10;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    protected array $allowedSorts = ['name', 'email', 'is_active', 'created_at', 'updated_at'];
    protected array $allowedPerPage = [10, 25, 50, 100];

    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
    ];

    public function updatingSearch()
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

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);
        $oldStatus = $user->is_active;
        $newStatus = ! $user->is_active;
        $status = $oldStatus ? 'deactivated' : 'activated';

        $user->update(['is_active' => $newStatus]);

        LoggerService::logUserAction(
            'toggle_status',
            'User',
            $userId,
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'target_user_email' => $user->email,
            ]
        );

        CacheService::clearUserCache($userId);
        CacheService::clearDashboardCache();

        $this->dispatch('$refresh');
        $this->showSuccessToast("User {$status} successfully!");
    }

    public function confirmDeleteUser($userId)
    {
        $user = User::findOrFail($userId);

        $this->showConfirm(
            'Delete User',
            "Are you sure you want to delete user '{$user->name}'? This action cannot be undone.",
            'deleteUser',
            ['userId' => $userId],
            'Yes, delete it!',
            'Cancel'
        );
    }

    public function deleteUser($params)
    {
        $userId = $params['userId'];
        $user = User::findOrFail($userId);

        LoggerService::logUserAction(
            'delete',
            'User',
            $userId,
            [
                'deleted_user_email' => $user->email,
                'deleted_user_name' => $user->name,
                'had_roles' => $user->roles->pluck('name')->toArray(),
            ],
            'warning'
        );

        CacheService::clearUserCache($userId);
        CacheService::clearDashboardCache();

        $user->delete();

        $this->dispatch('$refresh');
        $this->showSuccessToast('User deleted successfully!');
    }

    public function render()
    {
        if (! in_array($this->sortField, $this->allowedSorts, true)) {
            $this->sortField = 'name';
        }

        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'asc';
        }

        $users = User::query()
            ->select([
                'id',
                'name',
                'email',
                'is_active',
                'department_id',
                'created_at',
                'updated_at',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when(! $this->showInactive, function ($query) {
                $query->where('is_active', true);
            })
            ->with([
                'roles:id,name,display_name',
                'department:id,name',
            ])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
            ->onEachSide(0);

        return view('livewire.users.user-list', compact('users'));
    }
}
