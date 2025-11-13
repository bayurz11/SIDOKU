<?php

namespace App\Livewire\Roles;

use App\Domains\Role\Models\Role;
use App\Domains\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RoleForm extends Component
{
    public $roleId;
    public $name = '';
    public $display_name = '';
    public $description = '';
    public $is_active = true;
    public $selectedPermissions = [];
    public $showModal = false;
    public $isEditing = false;
    // Remove this property as it causes serialization issues
    // We'll compute it in render method instead

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('roles')->ignore($this->roleId),
            ],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'selectedPermissions' => 'array',
        ];
    }

    protected $messages = [
        'name.regex' => 'Role name must contain only lowercase letters, numbers, and hyphens.',
    ];

    public function mount($roleId = null)
    {
        if ($roleId) {
            $this->loadRole($roleId);
        }
    }

    public function loadRole($roleId)
    {
        $role = Role::findOrFail($roleId);

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->display_name = $role->display_name;
        $this->description = $role->description;
        $this->is_active = $role->is_active;
        $this->selectedPermissions = $role->permissions()->pluck('permissions.id')->toArray();
        $this->isEditing = true;
    }

    public function openModal($roleId = null)
    {
        $this->resetForm();

        if ($roleId) {
            $this->loadRole($roleId);
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->roleId = null;
        $this->name = '';
        $this->display_name = '';
        $this->description = '';
        $this->is_active = true;
        $this->selectedPermissions = [];
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    public function selectAllInGroup($group)
    {
        // Get permissions for this group from database
        $groupPermissions = Permission::where('is_active', true)
            ->where('group', $group)
            ->get();
        $groupIds = $groupPermissions->pluck('id')->toArray();

        // Check if all permissions in group are already selected
        $allSelected = !array_diff($groupIds, $this->selectedPermissions);

        if ($allSelected) {
            // Remove all permissions in this group
            $this->selectedPermissions = array_diff($this->selectedPermissions, $groupIds);
        } else {
            // Add all permissions in this group
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $groupIds));
        }
    }

    public function save()
    {
        $this->validate();

        // Prevent editing super-admin role
        if ($this->isEditing && $this->name === 'super-admin') {
            session()->flash('error', 'Cannot modify super-admin role.');
            return;
        }

        if ($this->isEditing) {
            $role = Role::findOrFail($this->roleId);
            $role->update([
                'name' => $this->name,
                'display_name' => $this->display_name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
        } else {
            $role = Role::create([
                'name' => $this->name,
                'display_name' => $this->display_name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
        }

        $role->permissions()->sync($this->selectedPermissions);

        session()->flash('message', $this->isEditing ? 'Role updated successfully.' : 'Role created successfully.');

        $this->closeModal();
        $this->dispatch('roleSaved');
    }

    public function render()
    {
        // Get permissions grouped by group for the view
        $permissions = Permission::where('is_active', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        $permissionsByGroup = $permissions->groupBy('group');

        return view('livewire.roles.role-form', compact('permissionsByGroup'));
    }

    protected $listeners = ['openRoleCreateForm' => 'openModal'];
}
