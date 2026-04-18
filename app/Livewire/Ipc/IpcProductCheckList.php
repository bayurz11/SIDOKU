<?php

namespace App\Livewire\Ipc;

use App\Domains\Ipc\Models\IpcProductCheck;
use App\Shared\Traits\WithAlerts;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

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

    protected array $allowedPerPage = [10, 25, 50, 100, 250, 500];

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

    public function updatingPerPage($value): void
    {
        $this->perPage = in_array((int) $value, $this->allowedPerPage, true) ? (int) $value : 10;
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

        $this->resetPage();
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
        $this->dispatch('openIpcProductDetail', id: $id);
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'filterLineGroup',
            'filterSubLine',
            'filterDateFrom',
            'filterDateTo',
        ]);

        $this->sortField = 'test_date';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->resetPage();
    }

    protected function buildFilteredQuery(): Builder
    {
        return IpcProductCheck::query()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where('product_name', 'like', $term);
            })
            ->when($this->filterLineGroup, fn($q) => $q->where('line_group', $this->filterLineGroup))
            ->when($this->filterSubLine, fn($q) => $q->where('sub_line', $this->filterSubLine))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('test_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('test_date', '<=', $this->filterDateTo));
    }

    protected function sanitizeState(): void
    {
        if (! in_array($this->sortField, $this->allowedSorts, true)) {
            $this->sortField = 'test_date';
        }

        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'desc';
        }

        if (! in_array($this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }
    }

    public function render()
    {
        $this->sanitizeState();

        $baseQuery = $this->buildFilteredQuery();

        $data = (clone $baseQuery)
            ->select([
                'id',
                'line_group',
                'sub_line',
                'test_date',
                'product_name',
                'shift',
                'avg_moisture_percent',
                'avg_weight_g',
            ])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
            ->onEachSide(0);

        $moistureSummary = (clone $baseQuery)
            ->whereNotNull('avg_moisture_percent')
            ->selectRaw('line_group, sub_line, AVG(avg_moisture_percent) as avg_moisture, COUNT(*) as total_samples')
            ->groupBy('line_group', 'sub_line')
            ->get();

        $highMoistureItems = (clone $baseQuery)
            ->where('avg_moisture_percent', '>=', 10)
            ->select([
                'id',
                'line_group',
                'sub_line',
                'product_name',
                'avg_moisture_percent',
                'test_date',
            ])
            ->latest('test_date')
            ->take(10)
            ->get();

        $chartLabels = $moistureSummary->map(function ($row) {
            $lineLabel = $this->lineGroups[$row->line_group] ?? $row->line_group;
            $subLabel = $row->sub_line ? ($this->subLinesTeh[$row->sub_line] ?? $row->sub_line) : null;

            return $subLabel ?: $lineLabel;
        })->values();

        $chartValues = $moistureSummary->map(fn($row) => round($row->avg_moisture, 2))->values();
        $chartCounts = $moistureSummary->map(fn($row) => (int) $row->total_samples)->values();

        return view('livewire.ipc.ipc-product-check-list', [
            'data'            => $data,
            'moistureSummary' => $moistureSummary,
            'chartLabels'     => $chartLabels,
            'chartValues'     => $chartValues,
            'chartCounts'     => $chartCounts,
            'highMoistureItems' => $highMoistureItems,
        ]);
    }
}
