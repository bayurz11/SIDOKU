<?php

namespace App\Livewire\Roles;

use App\Domains\Role\Models\Role;
use App\Domains\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RoleForm extends Component
{
    public $roleId = null;
    public $name = '';
    public $display_name = '';
    public $description = '';
    public $is_active = true;
    public $selectedPermissions = [];
    public $showModal = false;
    public $isEditing = false;

    protected $listeners = [
        'openRoleForm' => 'openModal',
    ];

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('roles', 'name')->ignore($this->roleId),
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

    public function openModal($roleId = null)
    {
        $this->resetForm();

        if ($roleId) {
            $this->loadRole($roleId);
            $this->isEditing = true;
        }

        $this->showModal = true;
    }

    public function loadRole($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->display_name = $role->display_name;
        $this->description = $role->description;
        $this->is_active = $role->is_active;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function resetForm()
    {
        $this->reset([
            'roleId',
            'name',
            'display_name',
            'description',
            'is_active',
            'selectedPermissions',
            'isEditing',
        ]);

        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function selectAllInGroup($group)
    {
        $groupPermissions = Permission::where('is_active', true)
            ->where('group', $group)
            ->pluck('id')
            ->toArray();

        $allSelected = !array_diff($groupPermissions, $this->selectedPermissions);

        if ($allSelected) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, $groupPermissions);
        } else {
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $groupPermissions));
        }
    }

    public function save()
    {
        $this->validate();

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

        $this->dispatch('roleSaved');
        $this->closeModal();

        session()->flash('message', $this->isEditing
            ? 'Role updated successfully.'
            : 'Role created successfully.');
    }

    public function render()
    {
        $permissions = Permission::where('is_active', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        return view('livewire.roles.role-form', [
            'permissionsByGroup' => $permissions,
        ]);
    }
}
