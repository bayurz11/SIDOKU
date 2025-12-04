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
    public ?string $filterLineGroup = null;
    public ?string $filterDateFrom  = null;
    public ?string $filterDateTo    = null;
    public ?string $filterCondition = null; // filter kondisi botol (opsional)

    public int $perPage        = 10;
    public string $sortField   = 'test_date';
    public string $sortDirection = 'desc';

    public array $lineGroups = [];
    public array $bottleConditions = [];

    protected array $allowedSorts = [
        'test_date',
        'product_name',
        'line_group',
        'shift',
        'bottle_condition',
    ];

    protected array $allowedPerPage = [10, 25, 50, 100];

    protected $queryString = [
        'search'          => ['except' => ''],
        'filterLineGroup' => ['except' => null],
        'filterDateFrom'  => ['except' => null],
        'filterDateTo'    => ['except' => null],
        'filterCondition' => ['except' => null],
        'perPage'         => ['except' => 10],
        'sortField'       => ['except' => 'test_date'],
        'sortDirection'   => ['except' => 'desc'],
    ];

    protected $listeners = [
        'tiup-botol:saved' => 'refreshList',
    ];

    public function mount(): void
    {
        $this->lineGroups        = TiupBotolCheck::LINE_GROUPS;
        $this->bottleConditions  = TiupBotolCheck::BOTTLE_CONDITIONS;
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

    public function updatingFilterCondition(): void
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

    public function render()
    {
        $baseQuery = TiupBotolCheck::query()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('product_name', 'like', $term);
                });
            })
            ->when(
                $this->filterLineGroup,
                fn($q) =>
                $q->where('line_group', $this->filterLineGroup)
            )
            ->when(
                $this->filterCondition,
                fn($q) =>
                $q->where('bottle_condition', $this->filterCondition)
            )
            ->when(
                $this->filterDateFrom,
                fn($q) =>
                $q->whereDate('test_date', '>=', $this->filterDateFrom)
            )
            ->when(
                $this->filterDateTo,
                fn($q) =>
                $q->whereDate('test_date', '<=', $this->filterDateTo)
            );

        // Data utama tabel
        $data = (clone $baseQuery)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // RINGKASAN UNTUK CHART: jumlah sampel per Line + Kondisi botol
        $conditionSummary = (clone $baseQuery)
            ->selectRaw('line_group, bottle_condition, COUNT(*) as total_samples')
            ->groupBy('line_group', 'bottle_condition')
            ->get();

        return view('livewire.ipc.tiup-botol-list', [
            'data'             => $data,
            'conditionSummary' => $conditionSummary,
        ]);
    }
}
