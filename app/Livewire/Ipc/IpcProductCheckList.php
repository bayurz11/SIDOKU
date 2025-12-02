<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use Livewire\WithPagination;
use App\Shared\Traits\WithAlerts;
use App\Domains\Ipc\Models\IpcProductCheck;

class IpcProductCheckList extends Component
{
    use WithPagination, WithAlerts;

    public string $search = '';
    public ?string $filterLineGroup = null;
    public ?string $filterSubLine   = null;
    public ?string $filterDateFrom  = null;
    public ?string $filterDateTo    = null;

    public int $perPage        = 10;
    public string $sortField   = 'test_date';
    public string $sortDirection = 'desc';

    public array $lineGroups = [];
    public array $subLinesTeh = [];

    /**
     * Kolom yang boleh digunakan untuk sorting.
     * Disesuaikan dengan struktur tabel terbaru (tanpa avg_ph, avg_brix, dll).
     */
    protected array $allowedSorts = [
        'test_date',
        'product_name',
        'line_group',
        'sub_line',
        'shift',
        'avg_moisture_percent',
        'avg_weight_g',
    ];

    protected array $allowedPerPage = [10, 25, 50, 100];

    protected $queryString = [
        'search'          => ['except' => ''],
        'filterLineGroup' => ['except' => null],
        'filterSubLine'   => ['except' => null],
        'filterDateFrom'  => ['except' => null],
        'filterDateTo'    => ['except' => null],
        'perPage'         => ['except' => 10],
        'sortField'       => ['except' => 'test_date'],
        'sortDirection'   => ['except' => 'desc'],
    ];

    protected $listeners = [
        'ipc:product_check_saved' => 'refreshList',
    ];

    public function mount(): void
    {
        // Diambil dari konstanta di Model (sekarang hanya LINE_TEH & LINE_POWDER)
        $this->lineGroups  = IpcProductCheck::LINE_GROUPS;
        $this->subLinesTeh = IpcProductCheck::SUB_LINES_TEH;
    }

    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterLineGroup(): void
    {
        // reset subline kalau ganti line group
        $this->filterSubLine = null;
        $this->resetPage();
    }

    public function updatingFilterSubLine(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        if (! in_array($this->perPage, $this->allowedPerPage)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSorts, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        $record = IpcProductCheck::findOrFail($id);
        $record->delete();

        $this->showSuccessToast('IPC record deleted!');
        $this->resetPage();
    }

    public function showDetail(int $id): void
    {
        $this->dispatch('openIpcProductCheckForm', id: $id);
    }

    public function render()
    {
        $query = IpcProductCheck::query()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('product_name', 'like', $term);
                });
            })
            ->when($this->filterLineGroup, fn($q) => $q->where('line_group', $this->filterLineGroup))
            ->when($this->filterSubLine, fn($q) => $q->where('sub_line', $this->filterSubLine))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('test_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('test_date', '<=', $this->filterDateTo))
            ->orderBy($this->sortField, $this->sortDirection);

        $data = $query->paginate($this->perPage);

        return view('livewire.ipc.ipc-product-check-list', compact('data'));
    }
}
