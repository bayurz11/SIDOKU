<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithPagination;
use App\Shared\Traits\WithAlerts;
use App\Domains\Document\Models\DocumentPrefixSetting; // sesuaikan namespace model jika beda

class DocumentPrefixList extends Component
{
    use WithPagination, WithAlerts;

    public string $search = '';
    public bool $showInactive = false;
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Kolom yang boleh di-sort
    protected array $allowedSorts = [
        'company_prefix',
        'format_nomor',
        'is_active',
        'created_at',
    ];

    // PerPage yang diizinkan
    protected array $allowedPerPage = [10, 25, 50];

    protected $queryString = [
        'search'        => ['except' => ''],
        'showInactive'  => ['except' => false],
        'perPage'       => ['except' => 10],
        'sortField'     => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'documentPrefix:saved' => 'refreshList',
    ];

    public function refreshList(): void
    {
        $this->resetPage();
    }

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
        // pastikan perPage hanya yang di-allow
        if (!in_array($this->perPage, $this->allowedPerPage)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->allowedSorts)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleStatus(int $id): void
    {
        $item = DocumentPrefixSetting::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        $this->showSuccessToast('Status prefix diperbarui!');
    }

    public function delete(int $id): void
    {
        $item = DocumentPrefixSetting::findOrFail($id);
        $item->delete();

        $this->showSuccessToast('Prefix setting dihapus!');
        $this->resetPage();
    }

    public function render()
    {
        $data = DocumentPrefixSetting::query()
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($sub) use ($term) {
                    $sub->where('company_prefix', 'like', $term)
                        ->orWhere('format_nomor', 'like', $term)
                        ->orWhere('example_output', 'like', $term);
                });
            })
            ->when(!$this->showInactive, fn($q) => $q->where('is_active', true))
            ->with(['documentType', 'department']) // asumsi ada relasi di model
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.document.document-prefix-list', compact('data'));
    }
}
