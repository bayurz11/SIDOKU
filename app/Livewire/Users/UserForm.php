<?php

namespace App\Livewire\Users;

use App\Domains\User\Models\User;
use App\Domains\Role\Models\Role;
use App\Domains\Department\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserForm extends Component
{
    public $userId;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $is_active = true;

    // NEW: Department
    public $department_id = null;

    public $selectedRoles = [];
    public $showModal = false;
    public $isEditing = false;

    // NEW: list departments untuk dropdown
    public $departments = [];

    protected $listeners = ['openUserCreateForm' => 'openModal'];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->userId),
            ],
            'password' => $this->isEditing ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
            'is_active' => 'boolean',
            'selectedRoles' => 'array',

            // NEW: validasi department
            'department_id' => ['required', 'exists:departments,id'],
        ];
    }

    public function mount($userId = null)
    {
        // NEW: load daftar department untuk dropdown
        $this->departments = Department::orderBy('name')->get();

        if ($userId) {
            $this->loadUser($userId);
        }
    }

    public function loadUser($userId)
    {
        $user = User::with('roles')->findOrFail($userId);

        $this->userId        = $user->id;
        $this->name          = $user->name;
        $this->email         = $user->email;
        $this->is_active     = $user->is_active;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();

        // NEW: isi department dari user
        $this->department_id = $user->department_id;

        $this->isEditing = true;
    }

    public function openModal($userId = null)
    {
        $this->resetForm();

        // Pastikan departments selalu terisi (jika form dibuka setelah reload)
        $this->departments = Department::orderBy('name')->get();

        if ($userId) {
            $this->loadUser($userId);
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
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = true;
        $this->selectedRoles = [];
        $this->isEditing = false;

        // NEW: reset department
        $this->department_id = null;

        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $user = User::findOrFail($this->userId);

            $user->update([
                'name'          => $this->name,
                'email'         => $this->email,
                'is_active'     => $this->is_active,
                'department_id' => $this->department_id, // NEW
            ]);

            if ($this->password) {
                $user->update([
                    'password' => Hash::make($this->password),
                ]);
            }
        } else {
            $user = User::create([
                'name'          => $this->name,
                'email'         => $this->email,
                'password'      => Hash::make($this->password),
                'is_active'     => $this->is_active,
                'department_id' => $this->department_id, // NEW
            ]);
        }

        $user->roles()->sync($this->selectedRoles);

        session()->flash(
            'message',
            $this->isEditing ? 'User updated successfully.' : 'User created successfully.'
        );

        $this->closeModal();
        $this->dispatch('userSaved');
    }

    public function render()
    {
        $roles = Role::where('is_active', true)->orderBy('name')->get();

        return view('livewire.users.user-form', [
            'roles'       => $roles,
            'departments' => $this->departments,
        ]);
    }
}
