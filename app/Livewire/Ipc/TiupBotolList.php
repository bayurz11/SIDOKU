<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use Livewire\WithPagination;
use App\Shared\Traits\WithAlerts;
use App\Domains\Ipc\Models\TiupBotolCheck;

class TiupBotolList extends Component
{
    use WithPagination, WithAlerts;

    public string $search = '';

    public ?string $filterDateFrom  = null;
    public ?string $filterDateTo    = null;
    public ?string $filterDropTest  = null; // TDK_BCR / BCR

    public int $perPage        = 10;
    public string $sortField   = 'tanggal';
    public string $sortDirection = 'desc';

    protected array $allowedSorts = [
        'tanggal',
        'nama_botol',
        'drop_test',
    ];

    protected array $allowedPerPage = [10, 25, 50, 100];

    protected $queryString = [
        'search'         => ['except' => ''],
        'filterDateFrom' => ['except' => null],
        'filterDateTo'   => ['except' => null],
        'filterDropTest' => ['except' => null],
        'perPage'        => ['except' => 10],
        'sortField'      => ['except' => 'tanggal'],
        'sortDirection'  => ['except' => 'desc'],
    ];

    protected $listeners = [
        'tiup-botol:saved' => 'refreshList',
        'openTiupBotolDetail' => 'open',
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
        $record = TiupBotolCheck::findOrFail($id);
        $record->delete();

        $this->showSuccessToast('Data tiup botol berhasil dihapus!');
        $this->resetPage();
    }

    public function showDetail(int $id): void
    {
        // nanti bisa diarahkan ke modal detail/form edit khusus Tiup Botol
        $this->dispatch('openTiupBotolForm', id: $id);
    }

    public function render()
    {
        $baseQuery = TiupBotolCheck::query()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama_botol', 'like', $term)
                        ->orWhere('catatan', 'like', $term);
                });
            })
            ->when(
                $this->filterDropTest,
                fn($q) =>
                $q->where('drop_test', $this->filterDropTest)
            )
            ->when(
                $this->filterDateFrom,
                fn($q) =>
                $q->whereDate('tanggal', '>=', $this->filterDateFrom)
            )
            ->when(
                $this->filterDateTo,
                fn($q) =>
                $q->whereDate('tanggal', '<=', $this->filterDateTo)
            );

        // data utama tabel
        $data = (clone $baseQuery)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // RINGKASAN UNTUK CHART: jumlah per drop_test
        $dropSummary = (clone $baseQuery)
            ->selectRaw('drop_test, COUNT(*) as total_samples')
            ->groupBy('drop_test')
            ->get();

        return view('livewire.ipc.tiup-botol-list', [
            'data'        => $data,
            'dropSummary' => $dropSummary,
        ]);
    }
}
