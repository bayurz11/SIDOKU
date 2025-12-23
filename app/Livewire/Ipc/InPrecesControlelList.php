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

    /**
     * Kolom yang boleh digunakan untuk sorting.
     * Disesuaikan dengan struktur tabel ipc_check_product.
     */
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

    protected array $allowedPerPage = [10, 25, 50, 100, 250];

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
        // Diambil dari konstanta di Model IpcProduct
        $this->lineGroups  = IpcProduct::LINE_GROUPS;
        $this->subLinesTeh = IpcProduct::SUB_LINES;

        // ✅ Default: tampilkan data bulan berjalan jika user belum set filter tanggal
        if (blank($this->filterDateFrom) && blank($this->filterDateTo)) {
            $this->filterDateFrom = Carbon::now()->startOfMonth()->toDateString();
            $this->filterDateTo   = Carbon::now()->endOfMonth()->toDateString();
        }
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
        if (! in_array($this->perPage, $this->allowedPerPage, true)) {
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

    /**
     * ✅ Optional: tombol "Bulan Ini" (berguna kalau queryString nyangkut bulan lama)
     */
    public function resetToCurrentMonth(): void
    {
        $this->filterDateFrom = Carbon::now()->startOfMonth()->toDateString();
        $this->filterDateTo   = Carbon::now()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $record = IpcProduct::findOrFail($id);
        $record->delete();

        $this->showSuccessToast('IPC record deleted!');
        $this->resetPage();
    }

    public function showDetail(int $id): void
    {
        $this->dispatch('openIpcProductForm', id: $id);
    }

    public function render()
    {
        // base query dengan filter
        $baseQuery = IpcProduct::query()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('product_name', 'like', $term);
                });
            })
            ->when($this->filterLineGroup, fn($q) => $q->where('line_group', $this->filterLineGroup))
            ->when($this->filterSubLine, fn($q) => $q->where('sub_line', $this->filterSubLine))
            // ✅ filter tanggal aman untuk DATE/DATETIME
            ->when($this->filterDateFrom && $this->filterDateTo, function ($q) {
                $from = $this->filterDateFrom . ' 00:00:00';
                $to   = $this->filterDateTo   . ' 23:59:59';
                $q->whereBetween('test_date', [$from, $to]);
            })
            // fallback kalau user isi salah satu saja
            ->when($this->filterDateFrom && ! $this->filterDateTo, fn($q) => $q->whereDate('test_date', '>=', $this->filterDateFrom))
            ->when(! $this->filterDateFrom && $this->filterDateTo, fn($q) => $q->whereDate('test_date', '<=', $this->filterDateTo));

        // data tabel (pagination + sorting)
        $data = (clone $baseQuery)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // RINGKASAN UNTUK CHART
        $summary = (clone $baseQuery)
            ->selectRaw('
                line_group,
                sub_line,
                AVG(avg_weight_g)      as avg_weight_g,
                AVG(avg_ph)            as avg_ph,
                AVG(avg_brix)          as avg_brix,
                AVG(avg_tds_ppm)       as avg_tds_ppm,
                AVG(avg_chlorine)      as avg_chlorine,
                AVG(avg_ozone)         as avg_ozone,
                AVG(avg_turbidity_ntu) as avg_turbidity_ntu,
                AVG(avg_salinity)      as avg_salinity,
                COUNT(*)               as total_samples
            ')
            ->groupBy('line_group', 'sub_line')
            ->get();

        return view('livewire.ipc.in-preces-controlel-list', [
            'data'    => $data,
            'summary' => $summary,
        ]);
    }
}
