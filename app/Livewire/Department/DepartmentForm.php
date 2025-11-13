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
    public string $editorId;

    protected $listeners = ['openDepartmentForm' => 'openForm'];

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:255|unique:departments,name,' . ($this->departmentId ?? 'NULL') . ',id',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ];
    }

    /**
     * Buka modal form
     */
    public function openForm($payload = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->editorId = 'editor-' . uniqid();
        $this->showModal = true;
        $this->isEditing = false;

        $departmentId = $payload['id'] ?? null;

        if ($departmentId) {
            $department = Department::findOrFail($departmentId);
            $this->departmentId = $department->id;
            $this->name = $department->name;
            $this->description = $department->description ?? '';
            $this->is_active = $department->is_active;
            $this->isEditing = true;
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

        if ($this->isEditing) {
            Department::findOrFail($this->departmentId)->update($data);
            LoggerService::logUserAction('update', 'Department', $this->departmentId, [
                'updated_name' => $this->name,
            ]);
        } else {
            $department = Department::create($data);
            LoggerService::logUserAction('create', 'Department', $department->id, [
                'created_name' => $this->name,
            ]);
        }

        CacheService::clearDashboardCache();
        $this->showSuccessToast('Department saved successfully!');
        $this->dispatch('departmentUpdated'); // refresh list
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset(['departmentId', 'name', 'description', 'is_active', 'isEditing']);
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.department.department-form');
    }
}
