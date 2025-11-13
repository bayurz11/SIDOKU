<?php

namespace App\Livewire\Department;

use Livewire\Component;
use App\Shared\Traits\WithAlerts;
use App\Domains\Department\Models\Department;
use App\Shared\Services\CacheService;
use App\Shared\Services\LoggerService;

class DepartmentForm extends Component
{
    use WithAlerts;

    public ?int $departmentId = null;
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?string $editorId = null;

    protected $listeners = [
        'openDepartmentForm' => 'openForm',
    ];

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:255|unique:departments,name,' . ($this->departmentId ?: 'NULL') . ',id',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ];
    }

    /**
     * Buka modal form
     */
    public function openForm(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->editorId = 'editor-' . uniqid();

        // default: create mode
        $this->showModal  = true;
        $this->isEditing  = false;
        $this->is_active  = true;

        if ($id) {
            $department = Department::findOrFail($id);

            $this->departmentId = $department->id;
            $this->name         = $department->name;
            $this->description  = $department->description ?? '';
            $this->is_active    = (bool) $department->is_active;
            $this->isEditing    = true;
        } else {
            // reset field khusus data
            $this->reset(['departmentId', 'name', 'description', 'isEditing']);
        }
    }

    /**
     * Simpan data
     */
    public function save(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'description' => $this->description,
            'is_active'   => $this->is_active,
        ];

        if ($this->isEditing && $this->departmentId) {
            Department::findOrFail($this->departmentId)->update($data);

            LoggerService::logUserAction('update', 'Department', $this->departmentId, [
                'updated_name' => $this->name,
            ]);
        } else {
            $department = Department::create($data);

            LoggerService::logUserAction('create', 'Department', $department->id, [
                'created_name' => $this->name,
            ]);

            // set id baru kalau mau dipakai lagi
            $this->departmentId = $department->id;
        }

        CacheService::clearDashboardCache();

        $this->showSuccessToast('Department saved successfully!');
        $this->dispatch('department:saved'); // event untuk refresh list
        $this->closeModal();
    }

    /**
     * Tutup modal
     */
    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'departmentId',
            'name',
            'description',
            'is_active',
            'isEditing',
            'showModal',
            'editorId',
        ]);

        // default state
        $this->is_active = true;
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.department.department-form');
    }
}
