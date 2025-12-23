<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use App\Shared\Traits\WithAlerts;
use App\Domains\Ipc\Models\IpcProduct;

class InPrecesControlelList extends Component
{
    use WithPagination, WithAlerts;

    public string $search = '';
    public ?string $filterLineGroup = null;
    public ?string $filterSubLine   = null;
    public ?string $filterDateFrom  = null;
    public ?string $filterDateTo    = null;

    public int $perPage          = 10;
    public string $sortField     = 'test_date';
    public string $sortDirection = 'desc';

    public array $lineGroups = [];
    public array $subLinesTeh = [];

    // ✅ Alert rules (ubah sesuai standar)
    protected array $alertRules = [
        'avg_ph' => ['label' => 'pH', 'type' => 'range', 'min' => 5.0,  'max' => 7.5,  'unit' => ''],
        'avg_tds_ppm' => ['label' => 'TDS', 'type' => 'max', 'max' => 10.0, 'unit' => 'ppm'],
        'avg_chlorine' => ['label' => 'Klorin', 'type' => 'max', 'max' => 0.1, 'unit' => 'mg/L'],
        'avg_ozone' => ['label' => 'Ozon', 'type' => 'range', 'min' => 0.05, 'max' => 0.30, 'unit' => 'ppm'],
        'avg_turbidity_ntu' => ['label' => 'Kekeruhan', 'type' => 'max', 'max' => 1.5, 'unit' => 'NTU'],
    ];

    protected array $allowedSorts = [
        'test_date',
        'product_name',
        'line_group',
        'sub_line',
        'shift',
        'avg_weight_g',
        'avg_ph',
        'avg_brix',
        'avg_tds_ppm',
        'avg_chlorine',
        'avg_ozone',
        'avg_turbidity_ntu',
        'avg_salinity',
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
        'ipc:product_saved' => 'refreshList',
    ];

    public function mount(): void
    {
        $this->lineGroups  = IpcProduct::LINE_GROUPS;
        $this->subLinesTeh = IpcProduct::SUB_LINES;

        // ✅ Default bulan berjalan (real-time) jika user belum set tanggal
        if (blank($this->filterDateFrom) && blank($this->filterDateTo)) {
            $this->filterDateFrom = now()->startOfMonth()->toDateString();
            $this->filterDateTo   = now()->endOfMonth()->toDateString();
        }
    }

    // ✅ Tombol "Bulan Ini"
    public function resetToCurrentMonth(): void
    {
        $this->filterDateFrom = now()->startOfMonth()->toDateString();
        $this->filterDateTo   = now()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function refreshList(): void
    {
        $this->resetPage();
    }

    // reset pagination kalau filter berubah
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingFilterLineGroup(): void
    {
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
        if (!in_array($this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->allowedSorts, true)) return;

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        $record = IpcProduct::findOrFail($id);
        $record->delete();

        $this->showSuccessToast('IPC record deleted!');
        $this->resetPage();
    }

    protected function buildBaseQuery()
    {
        return IpcProduct::query()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where('product_name', 'like', $term);
            })
            ->when($this->filterLineGroup, fn($q) => $q->where('line_group', $this->filterLineGroup))
            ->when($this->filterSubLine, fn($q) => $q->where('sub_line', $this->filterSubLine))

            // ✅ tanggal aman untuk DATETIME juga
            ->when($this->filterDateFrom && $this->filterDateTo, function ($q) {
                $from = $this->filterDateFrom . ' 00:00:00';
                $to   = $this->filterDateTo   . ' 23:59:59';
                $q->whereBetween('test_date', [$from, $to]);
            })
            ->when($this->filterDateFrom && !$this->filterDateTo, fn($q) => $q->whereDate('test_date', '>=', $this->filterDateFrom))
            ->when(!$this->filterDateFrom && $this->filterDateTo, fn($q) => $q->whereDate('test_date', '<=', $this->filterDateTo));
    }

    protected function buildAlertQuery($baseQuery)
    {
        return (clone $baseQuery)->where(function ($q) {
            foreach ($this->alertRules as $field => $rule) {
                if ($rule['type'] === 'range') {
                    $q->orWhere(function ($qq) use ($field, $rule) {
                        $qq->whereNotNull($field)
                            ->where(function ($qqq) use ($field, $rule) {
                                $qqq->where($field, '<', $rule['min'])
                                    ->orWhere($field, '>', $rule['max']);
                            });
                    });
                } elseif ($rule['type'] === 'max') {
                    $q->orWhere(function ($qq) use ($field, $rule) {
                        $qq->whereNotNull($field)
                            ->where($field, '>', $rule['max']);
                    });
                }
            }
        });
    }

    public function render()
    {
        $baseQuery = $this->buildBaseQuery();

        // ✅ data tabel: hanya sesuai filter (bulan berjalan default)
        $data = (clone $baseQuery)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // ✅ summary chart: sesuai filter yang sama
        $summary = (clone $baseQuery)
            ->selectRaw('
                line_group,
                sub_line,
                COUNT(*) as total_samples
            ')
            ->groupBy('line_group', 'sub_line')
            ->get();

        // ✅ alert DB: sesuai filter yang sama (bukan dari paginator)
        $alertRows = $this->buildAlertQuery($baseQuery)
            ->select([
                'id',
                'test_date',
                'line_group',
                'sub_line',
                'product_name',
                'avg_ph',
                'avg_tds_ppm',
                'avg_chlorine',
                'avg_ozone',
                'avg_turbidity_ntu'
            ])
            ->orderByDesc('test_date')
            ->limit(50)
            ->get()
            ->map(function ($row) {
                $violations = [];
                foreach ($this->alertRules as $field => $rule) {
                    $val = $row->{$field};
                    if (is_null($val)) continue;

                    if ($rule['type'] === 'range' && ($val < $rule['min'] || $val > $rule['max'])) {
                        $violations[] = "{$rule['label']} {$val}{$rule['unit']} (std {$rule['min']}–{$rule['max']})";
                    }
                    if ($rule['type'] === 'max' && ($val > $rule['max'])) {
                        $violations[] = "{$rule['label']} {$val}{$rule['unit']} (max {$rule['max']})";
                    }
                }
                $row->violations = $violations;
                return $row;
            });

        return view('livewire.ipc.in-preces-controlel-list', [
            'data'      => $data,
            'summary'   => $summary,
            'alertRows' => $alertRows,
        ]);
    }
}
