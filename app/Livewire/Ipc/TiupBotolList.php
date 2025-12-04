<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use Livewire\WithPagination;
use App\Shared\Traits\WithAlerts;
use App\Domains\Ipc\Models\TiupBotolCheck;

class TiupBotolCheckList extends Component
{
    use WithPagination, WithAlerts;

    public string $search = '';
    public ?string $filterDateFrom  = null;
    public ?string $filterDateTo    = null;

    public ?string $filterDropTest        = null; // TDK_BCR / BCR
    public ?string $filterPenyebaranRata  = null; // OK / NOK
    public ?string $filterBottomMenonjol  = null; // OK / NOK
    public ?string $filterTidakAdaMaterial = null; // OK / NOK

    public int $perPage        = 10;
    public string $sortField   = 'tanggal';
    public string $sortDirection = 'desc';

    /**
     * Kolom yang boleh digunakan untuk sorting.
     */
    protected array $allowedSorts = [
        'tanggal',
        'nama_botol',
        'drop_test',
        'penyebaran_rata',
        'bottom_tidak_menonjol',
        'tidak_ada_material',
    ];

    protected array $allowedPerPage = [10, 25, 50, 100];

    protected $queryString = [
        'search'              => ['except' => ''],
        'filterDateFrom'      => ['except' => null],
        'filterDateTo'        => ['except' => null],
        'filterDropTest'      => ['except' => null],
        'filterPenyebaranRata' => ['except' => null],
        'filterBottomMenonjol' => ['except' => null],
        'filterTidakAdaMaterial' => ['except' => null],
        'perPage'             => ['except' => 10],
        'sortField'           => ['except' => 'tanggal'],
        'sortDirection'       => ['except' => 'desc'],
    ];

    protected $listeners = [
        'tiup-botol:saved' => 'refreshList', // panggil ini setelah create/update form
    ];

    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
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

    public function updatingFilterDropTest(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPenyebaranRata(): void
    {
        $this->resetPage();
    }

    public function updatingFilterBottomMenonjol(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTidakAdaMaterial(): void
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

    public function delete(int $id): void
    {
        $record = TiupBotolCheck::findOrFail($id);
        $record->delete();

        $this->showSuccessToast('Data Tiup Botol berhasil dihapus!');
        $this->resetPage();
    }

    public function showDetail(int $id): void
    {
        // sesuaikan dengan nama event form detail/edit kamu
        $this->dispatch('openTiupBotolForm', id: $id);
    }

    public function render()
    {
        // base query dengan semua filter
        $baseQuery = TiupBotolCheck::query()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama_botol', 'like', $term)
                        ->orWhere('catatan', 'like', $term);
                });
            })
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('tanggal', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('tanggal', '<=', $this->filterDateTo))
            ->when($this->filterDropTest, fn($q) => $q->where('drop_test', $this->filterDropTest))
            ->when($this->filterPenyebaranRata, fn($q) => $q->where('penyebaran_rata', $this->filterPenyebaranRata))
            ->when($this->filterBottomMenonjol, fn($q) => $q->where('bottom_tidak_menonjol', $this->filterBottomMenonjol))
            ->when($this->filterTidakAdaMaterial, fn($q) => $q->where('tidak_ada_material', $this->filterTidakAdaMaterial));

        // data utama tabel
        $data = (clone $baseQuery)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.ipc.tiup-botol-list', [
            'data' => $data,
        ]);
    }
}
