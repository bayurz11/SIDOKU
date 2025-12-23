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

    // ✅ contoh alert kadar air (ubah field kalau beda)
    public float $moistureThreshold = 10.0; // >= 10%

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
        $this->subLinesTeh = IpcProduct::SUB_LINES ?? [];

        // ✅ DEFAULT: langsung bulan berjalan (realtime)
        $this->applyCurrentMonthDefaults();
    }

    /**
     * ✅ Pastikan kalau user masuk bulan baru, default ikut bulan baru
     * (selama user belum set tanggal manual)
     */
    public function hydrate(): void
    {
        // Jika filter kosong / null, kita set lagi ke bulan berjalan
        if (blank($this->filterDateFrom) && blank($this->filterDateTo)) {
            $this->applyCurrentMonthDefaults();
        }
    }

    private function applyCurrentMonthDefaults(): void
    {
        $this->filterDateFrom = now()->startOfMonth()->toDateString();
        $this->filterDateTo   = now()->endOfMonth()->toDateString();
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
        if (!in_array($this->perPage, $this->allowedPerPage, true)) $this->perPage = 10;
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->allowedSorts, true)) return;

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // ✅ tombol Bulan Ini
    public function resetToCurrentMonth(): void
    {
        $this->applyCurrentMonthDefaults();
        $this->resetPage();
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
        $q = IpcProduct::query();

        // search
        if ($this->search) {
            $term = '%' . $this->search . '%';
            $q->where('product_name', 'like', $term);
        }

        // line/subline
        if ($this->filterLineGroup) $q->where('line_group', $this->filterLineGroup);
        if ($this->filterSubLine)   $q->where('sub_line', $this->filterSubLine);

        /**
         * ✅ WAJIB: default bulan berjalan tetap berlaku walau user belum filter
         * dan aman jika user isi manual.
         */
        $from = $this->filterDateFrom ?: now()->startOfMonth()->toDateString();
        $to   = $this->filterDateTo   ?: now()->endOfMonth()->toDateString();

        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt   = Carbon::parse($to)->endOfDay();

        $q->whereBetween('test_date', [$fromDt, $toDt]);

        return $q;
    }

    protected function buildHighMoistureAlertQuery($baseQuery)
    {
        // ✅ FIELD kadar air: sesuaikan jika berbeda
        // contoh field: avg_moisture_percent
        return (clone $baseQuery)
            ->whereNotNull('avg_moisture_percent')
            ->where('avg_moisture_percent', '>=', $this->moistureThreshold);
    }

    public function render()
    {
        $baseQuery = $this->buildBaseQuery();

        // ✅ TABLE
        $data = (clone $baseQuery)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // ✅ SUMMARY CHART (ikut filter DB)
        $summary = (clone $baseQuery)
            ->selectRaw('line_group, sub_line, COUNT(*) as total_samples')
            ->groupBy('line_group', 'sub_line')
            ->get();

        // ✅ ALERT (ikut filter DB, bukan pagination)
        $highMoistureItems = $this->buildHighMoistureAlertQuery($baseQuery)
            ->select(['id', 'test_date', 'line_group', 'sub_line', 'product_name', 'avg_moisture_percent'])
            ->orderByDesc('test_date')
            ->limit(50)
            ->get();

        return view('livewire.ipc.in-preces-controlel-list', [
            'data'              => $data,
            'summary'           => $summary,
            'highMoistureItems' => $highMoistureItems,
        ]);
    }
}
