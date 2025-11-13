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

    public $search = '';
    public $showInactive = false;
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
    ];

    public function updatingSearch()
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

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);
        $oldStatus = $user->is_active;
        $newStatus = !$user->is_active;
        $status = $oldStatus ? 'deactivated' : 'activated';
        
        $user->update(['is_active' => $newStatus]);
        
        // Log the action
        LoggerService::logUserAction(
            'toggle_status',
            'User',
            $userId,
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'target_user_email' => $user->email
            ]
        );
        
        // Clear user cache
        CacheService::clearUserCache($userId);
        CacheService::clearDashboardCache();
        
        // Refresh the component to show updated data
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
        
        // Log the action before deletion
        LoggerService::logUserAction(
            'delete',
            'User',
            $userId,
            [
                'deleted_user_email' => $user->email,
                'deleted_user_name' => $user->name,
                'had_roles' => $user->roles->pluck('name')->toArray()
            ],
            'warning'
        );
        
        // Clear user cache before deletion
        CacheService::clearUserCache($userId);
        CacheService::clearDashboardCache();
        
        $user->delete();
        
        // Refresh the component to show updated data
        $this->dispatch('$refresh');
        
        $this->showSuccessToast('User deleted successfully!');
    }

    public function render()
    {
        // Optimize query with proper select and joins
        $users = User::query()
            ->select(['id', 'name', 'email', 'is_active', 'created_at', 'updated_at'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when(!$this->showInactive, function ($query) {
                $query->where('is_active', true);
            })
            ->with(['roles:id,name,display_name']) // Only select needed columns
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.users.user-list', compact('users'));
    }
}
