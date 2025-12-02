<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use App\Shared\Traits\WithAlerts;
use App\Domains\Ipc\Models\IpcProductCheck;
use Illuminate\Validation\Rule;

class IpcProductCheckForm extends Component
{
    use WithAlerts;

    public ?int $ipcId = null;

    public ?string $line_group = null;
    public ?string $sub_line   = null;
    public ?string $test_date  = null;
    public string $product_name = '';
    public ?int $shift         = null;

    // --- FIELD HASIL RINGKAS (TETAP ADA) ---
    public ?float $avg_moisture_percent = null;
    public ?float $avg_weight_g         = null;


    // --- FIELD KHUSUS HITUNG KADAR AIR LINE TEH / POWDER (NEW) ---
    public ?float $cup_weight             = null; // berat cawan porselin
    public ?float $product_weight         = null; // berat produk
    public ?float $total_cup_plus_product = null; // total (cawan + produk)
    public ?float $weighing_1             = null; // penimbangan 1
    public ?float $weighing_2             = null; // penimbangan 2

    public ?string $notes               = null;

    public bool $showModal = false;
    public bool $isEditing = false;

    public array $lineGroups = [];
    public array $subLinesTeh = [];

    // Line yang pakai perhitungan kadar air otomatis
    protected array $moistureLines = ['LINE_TEH', 'LINE_POWDER']; // NEW

    protected $listeners = [
        'openIpcProductCheckForm' => 'openForm',
    ];

    protected function rules(): array
    {
        return [
            'line_group'   => ['required', Rule::in(array_keys(IpcProductCheck::LINE_GROUPS))],
            'sub_line'     => ['nullable', Rule::in(array_keys(IpcProductCheck::SUB_LINES_TEH))],
            'test_date'    => ['required', 'date'],
            'product_name' => ['required', 'string', 'max:150'],
            'shift'        => ['nullable', 'integer', 'min:1', 'max:3'],

            'avg_moisture_percent' => ['nullable', 'numeric', 'min:0'],
            'avg_weight_g'         => ['nullable', 'numeric', 'min:0'],

            // NEW: field kalkulasi kadar air
            'cup_weight'             => ['nullable', 'numeric', 'min:0'],
            'product_weight'         => ['nullable', 'numeric', 'min:0'],
            'total_cup_plus_product' => ['nullable', 'numeric', 'min:0'],
            'weighing_1'             => ['nullable', 'numeric', 'min:0'],
            'weighing_2'             => ['nullable', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        $this->lineGroups = IpcProductCheck::LINE_GROUPS;
        $this->subLinesTeh = IpcProductCheck::SUB_LINES_TEH;
    }

    /**
     * Livewire hook: setiap ada field yang berubah.
     */
    public function updated($field): void
    {
        if ($field === 'line_group' && $this->line_group !== 'LINE_TEH') {
            $this->sub_line = null;
        }

        if (in_array($field, [
            'line_group',
            'cup_weight',
            'product_weight',
            'weighing_1',
            'weighing_2',
        ], true)) {
            $this->recalcMoisture();
        }
    }


    /**
     * Hitung ulang total (cawan + produk) dan kadar air (%)
     * Rumus resmi:
     *   (Berat Cawan + Berat Produk − (P1 + P2) / 2) ÷ Berat Produk × 100
     */
    protected function recalcMoisture(): void
    {
        // Hanya hitung otomatis untuk line tertentu
        if (! in_array($this->line_group, $this->moistureLines, true)) {
            $this->total_cup_plus_product = null;
            $this->avg_moisture_percent = null;
            return;
        }

        /**
         * 1) Hitung Total (Berat Cawan + Berat Produk)
         */
        if ($this->cup_weight !== null && $this->product_weight !== null) {
            $this->total_cup_plus_product = round(
                $this->cup_weight + $this->product_weight,
                3
            );

            // Berat produk ringkasan
            $this->avg_weight_g = $this->product_weight;
        } else {
            $this->total_cup_plus_product = null;
            $this->avg_weight_g = null;
        }

        /**
         * 2) Hitung kadar air jika semua komponen lengkap
         */
        if (
            $this->total_cup_plus_product !== null &&
            $this->weighing_1 !== null &&
            $this->weighing_2 !== null &&
            $this->product_weight !== null &&
            $this->product_weight > 0
        ) {
            // Rata-rata P1 dan P2: (P1 + P2) / 2
            $avgWeighing = ($this->weighing_1 + $this->weighing_2) / 2;

            // Rumus kadar air:
            // (Total - rata-rata penimbangan) / Berat Produk × 100
            $moisture = (
                ($this->total_cup_plus_product - $avgWeighing)
                / $this->product_weight
            ) * 100;

            $this->avg_moisture_percent = round($moisture, 2);
        } else {
            $this->avg_moisture_percent = null;
        }
    }


    public function openForm(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->showModal = true;
        $this->isEditing = false;

        if ($id) {
            // MODE EDIT
            $record = IpcProductCheck::findOrFail($id);

            $this->ipcId                = $record->id;
            $this->line_group           = $record->line_group;
            $this->sub_line             = $record->sub_line;
            $this->test_date            = optional($record->test_date)?->format('Y-m-d');
            $this->product_name         = $record->product_name;
            $this->shift                = $record->shift;

            $this->avg_moisture_percent = $record->avg_moisture_percent;
            $this->avg_weight_g         = $record->avg_weight_g;

            $this->notes                = $record->notes;

            // Field kalkulasi tidak di-load karena belum ada di DB (optional)
            $this->cup_weight             = null;
            $this->product_weight         = $this->avg_weight_g;
            $this->total_cup_plus_product = null;
            $this->weighing_1             = null;
            $this->weighing_2             = null;

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
                'avg_moisture_percent',
                'avg_weight_g',
                'cup_weight',
                'product_weight',
                'total_cup_plus_product',
                'weighing_1',
                'weighing_2',
                'notes',
            ]);
        }
    }

    public function save(): void
    {
        // Hitung ulang kadar air dulu, baru validasi
        $this->recalcMoisture(); // NEW

        $this->validate();

        // Kalau line TEH, sub_line boleh diwajibkan (logic lama)
        if ($this->line_group === 'LINE_TEH' && ! $this->sub_line) {
            $this->addError('sub_line', 'Sub-line wajib dipilih untuk Line Teh.');
            return;
        }

        $payload = [
            'line_group'           => $this->line_group,
            'sub_line'             => $this->line_group === 'LINE_TEH' ? $this->sub_line : null,
            'test_date'            => $this->test_date,
            'product_name'         => $this->product_name,
            'shift'                => $this->shift,
            'avg_moisture_percent' => $this->avg_moisture_percent,
            'avg_weight_g'         => $this->avg_weight_g,
            'notes'                => $this->notes,
        ];

        if ($this->isEditing && $this->ipcId) {
            $record = IpcProductCheck::findOrFail($this->ipcId);
            $record->update($payload);

            $this->showSuccessToast('IPC record updated successfully!');
        } else {
            $payload['created_by'] = auth()->id();

            $record = IpcProductCheck::create($payload);

            $this->showSuccessToast('IPC record created successfully!');
        }

        $this->dispatch('ipc:product_check_saved');
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
            'avg_moisture_percent',
            'avg_weight_g',
            'cup_weight',
            'product_weight',
            'total_cup_plus_product',
            'weighing_1',
            'weighing_2',
            'notes',
            'showModal',
            'isEditing',
        ]);

        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.ipc.ipc-product-check-form', [
            'lineGroups'  => $this->lineGroups,
            'subLinesTeh' => $this->subLinesTeh,
        ]);
    }
}
