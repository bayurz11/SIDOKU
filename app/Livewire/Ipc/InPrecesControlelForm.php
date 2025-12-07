<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use App\Shared\Traits\WithAlerts;
use App\Domains\Ipc\Models\IpcProduct;
use Illuminate\Validation\Rule;

class InPrecesControlelForm extends Component
{
    use WithAlerts;

    public ?int $ipcId = null;

    public ?string $line_group   = null;
    public ?string $sub_line     = null;
    public ?string $test_date    = null;
    public string $product_name  = '';
    public ?int $shift           = null;

    // --- FIELD PARAMETER HASIL UJI ---
    public ?float $avg_weight_g      = null; // Line Teh & Powder
    public ?float $avg_ph            = null; // Powder, Minuman Berperisa, AMDK, Condiments
    public ?float $avg_brix          = null; // Powder, Minuman Berperisa, AMDK, Condiments
    public ?float $avg_tds_ppm       = null; // Minuman Berperisa, AMDK, Condiments
    public ?float $avg_chlorine      = null; // AMDK
    public ?float $avg_ozone         = null; // AMDK
    public ?float $avg_turbidity_ntu = null; // AMDK
    public ?float $avg_salinity      = null; // Condiments

    public ?string $notes = null; // opsional – pastikan ada kolom di migration jika dipakai

    public bool $showModal = false;
    public bool $isEditing = false;

    public array $lineGroups = [];
    public array $subLinesTeh = [];

    protected $listeners = [
        'openIpcProductForm' => 'openForm', // disamakan dengan list component baru
    ];

    protected function rules(): array
    {
        return [
            'line_group'   => ['required', Rule::in(IpcProduct::LINE_GROUPS)],
            'sub_line'     => ['nullable', Rule::in(IpcProduct::SUB_LINES)],
            'test_date'    => ['required', 'date'],
            'product_name' => ['required', 'string', 'max:150'],
            'shift'        => ['nullable', 'integer', 'min:1', 'max:3'],

            'avg_weight_g'      => ['nullable', 'numeric', 'min:0'],
            'avg_ph'            => ['nullable', 'numeric', 'min:0'],
            'avg_brix'          => ['nullable', 'numeric', 'min:0'],
            'avg_tds_ppm'       => ['nullable', 'numeric', 'min:0'],
            'avg_chlorine'      => ['nullable', 'numeric', 'min:0'],
            'avg_ozone'         => ['nullable', 'numeric', 'min:0'],
            'avg_turbidity_ntu' => ['nullable', 'numeric', 'min:0'],
            'avg_salinity'      => ['nullable', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        $this->lineGroups  = IpcProduct::LINE_GROUPS;
        $this->subLinesTeh = IpcProduct::SUB_LINES;
    }

    public function openForm(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->showModal = true;
        $this->isEditing = false;

        if ($id) {
            // MODE EDIT
            $record = IpcProduct::findOrFail($id);

            $this->ipcId        = $record->id;
            $this->line_group   = $record->line_group;
            $this->sub_line     = $record->sub_line;
            $this->test_date    = optional($record->test_date)?->format('Y-m-d');
            $this->product_name = $record->product_name;
            $this->shift        = $record->shift;

            $this->avg_weight_g      = $record->avg_weight_g;
            $this->avg_ph            = $record->avg_ph;
            $this->avg_brix          = $record->avg_brix;
            $this->avg_tds_ppm       = $record->avg_tds_ppm;
            $this->avg_chlorine      = $record->avg_chlorine;
            $this->avg_ozone         = $record->avg_ozone;
            $this->avg_turbidity_ntu = $record->avg_turbidity_ntu;
            $this->avg_salinity      = $record->avg_salinity;

            $this->notes = $record->notes ?? null;

            $this->isEditing = true;
        } else {
            // MODE CREATE
            $this->reset([
                'ipcId',
                'line_group',
                'sub_line',
                'test_date',
                'product_name',
                'shift',
                'avg_weight_g',
                'avg_ph',
                'avg_brix',
                'avg_tds_ppm',
                'avg_chlorine',
                'avg_ozone',
                'avg_turbidity_ntu',
                'avg_salinity',
                'notes',
            ]);

            $this->isEditing = false;
        }
    }

    public function save(): void
    {
        $this->validate();

        // Kalau line TEH, wajib pilih sub_line
        if ($this->line_group === 'LINE_TEH' && ! $this->sub_line) {
            $this->addError('sub_line', 'Sub-line wajib dipilih untuk Line Teh.');
            return;
        }

        $payload = [
            'line_group'   => $this->line_group,
            'sub_line'     => $this->line_group === 'LINE_TEH' ? $this->sub_line : null,
            'test_date'    => $this->test_date,
            'product_name' => $this->product_name,
            'shift'        => $this->shift,

            'avg_weight_g'      => $this->avg_weight_g,
            'avg_ph'            => $this->avg_ph,
            'avg_brix'          => $this->avg_brix,
            'avg_tds_ppm'       => $this->avg_tds_ppm,
            'avg_chlorine'      => $this->avg_chlorine,
            'avg_ozone'         => $this->avg_ozone,
            'avg_turbidity_ntu' => $this->avg_turbidity_ntu,
            'avg_salinity'      => $this->avg_salinity,

            'notes' => $this->notes,
        ];

        if ($this->isEditing && $this->ipcId) {
            $record = IpcProduct::findOrFail($this->ipcId);
            $record->update($payload);

            $this->showSuccessToast('IPC product updated successfully!');
        } else {
            $payload['created_by'] = auth()->id();

            $record = IpcProduct::create($payload);

            $this->showSuccessToast('IPC product created successfully!');
        }

        // sinkron dengan list: InProcessControlList
        $this->dispatch('ipc:product_saved');

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'ipcId',
            'line_group',
            'sub_line',
            'test_date',
            'product_name',
            'shift',
            'avg_weight_g',
            'avg_ph',
            'avg_brix',
            'avg_tds_ppm',
            'avg_chlorine',
            'avg_ozone',
            'avg_turbidity_ntu',
            'avg_salinity',
            'notes',
            'showModal',
            'isEditing',
        ]);

        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.ipc.in-preces-controlel-form', [
            'lineGroups'  => $this->lineGroups,
            'subLinesTeh' => $this->subLinesTeh,
        ]);
    }
}
