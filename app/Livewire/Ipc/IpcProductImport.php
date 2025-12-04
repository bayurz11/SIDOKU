<?php

namespace App\Livewire\Ipc;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Shared\Traits\WithAlerts;
use App\Domains\Ipc\Models\IpcProductCheck;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class IpcProductImport extends Component
{
    use WithFileUploads, WithAlerts;

    public bool $showModal = false;

    // file excel yang diupload
    public $excel_file;

    // statistik sederhana
    public int $importedCount = 0;
    public int $skippedCount  = 0;

    // jangan pakai $errors
    public array $importErrors = [];

    /**
     * Line yang pakai perhitungan kadar air otomatis
     * (sama seperti di IpcProductCheckForm)
     */
    protected array $moistureLines = ['LINE_TEH', 'LINE_POWDER'];

    protected $listeners = [
        // sesuaikan dengan event dari tombol: $dispatch('openIpcProductImport')
        'openIpcProductImport' => 'openModal',
    ];

    protected function rules(): array
    {
        return [
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ];
    }

    /**
     * Buka modal import
     */
    public function openModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->showModal      = true;
        $this->excel_file     = null;
        $this->importedCount  = 0;
        $this->skippedCount   = 0;
        $this->importErrors   = [];
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'excel_file',
            'importedCount',
            'skippedCount',
            'importErrors',
            'showModal',
        ]);

        $this->showModal = false;
    }

    /**
     * Import data IPC dari Excel
     *
     * Header minimal:
     *  - line_group      (wajib, key dari IpcProductCheck::LINE_GROUPS)
     *  - test_date       (wajib)
     *  - product_name    (wajib)
     *
     * Opsional:
     *  - sub_line        (key dari SUB_LINES_TEH, wajib kalau line_group = LINE_TEH)
     *  - shift           (1-3)
     *  - cup_weight
     *  - product_weight
     *  - weighing_1
     *  - weighing_2
     *  - notes
     */
    public function import(): void
    {
        $this->validate();

        Storage::disk('local')->makeDirectory('imports');

        $path = $this->excel_file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($path);

        if (!Storage::disk('local')->exists($path)) {
            $this->addError('excel_file', 'File upload tidak ditemukan di server. Coba upload ulang.');
            return;
        }

        $this->importedCount = 0;
        $this->skippedCount  = 0;
        $this->importErrors  = [];

        $sheets = Excel::toArray([], $fullPath);
        $rows   = $sheets[0] ?? [];

        if (count($rows) <= 1) {
            $this->addError('excel_file', 'File Excel kosong atau tidak memiliki data.');
            Storage::disk('local')->delete($path);
            return;
        }

        // header
        $headerRow = array_map(function ($v) {
            return strtolower(trim((string) $v));
        }, $rows[0]);

        $col = function (string $name) use ($headerRow): ?int {
            $idx = array_search(strtolower($name), $headerRow);
            return $idx === false ? null : $idx;
        };

        $idxLineGroup   = $col('line_group');
        $idxSubLine     = $col('sub_line');
        $idxTestDate    = $col('test_date');
        $idxProductName = $col('product_name');
        $idxShift       = $col('shift');
        $idxCupWeight   = $col('cup_weight');
        $idxProdWeight  = $col('product_weight');
        $idxWeigh1      = $col('weighing_1');
        $idxWeigh2      = $col('weighing_2');
        $idxNotes       = $col('notes');

        if (is_null($idxLineGroup) || is_null($idxTestDate) || is_null($idxProductName)) {
            $this->addError(
                'excel_file',
                'Kolom minimal "line_group", "test_date", dan "product_name" harus ada di header.'
            );
            Storage::disk('local')->delete($path);
            return;
        }

        $validLineGroups = array_keys(IpcProductCheck::LINE_GROUPS);
        $validSubLines   = array_keys(IpcProductCheck::SUB_LINES_TEH);

        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue; // header
            }

            // skip baris kosong
            if (!array_filter($row, fn($v) => trim((string) $v) !== '')) {
                continue;
            }

            try {
                $lineGroup = strtoupper(trim((string) ($row[$idxLineGroup] ?? '')));
                $testDateRaw = $row[$idxTestDate] ?? null;
                $productName = trim((string) ($row[$idxProductName] ?? ''));

                if ($lineGroup === '' || !in_array($lineGroup, $validLineGroups, true)) {
                    $this->skippedCount++;
                    $this->importErrors[] =
                        "Baris " . ($i + 1) . ": line_group '{$lineGroup}' tidak valid / tidak dikenal.";
                    continue;
                }

                $testDate = $this->parseExcelDate($testDateRaw);
                if (!$testDate) {
                    $this->skippedCount++;
                    $this->importErrors[] =
                        "Baris " . ($i + 1) . ": test_date tidak valid.";
                    continue;
                }

                if ($productName === '') {
                    $this->skippedCount++;
                    $this->importErrors[] =
                        "Baris " . ($i + 1) . ": product_name kosong, di-skip.";
                    continue;
                }

                // sub-line
                $subLine = null;
                if ($idxSubLine !== null && isset($row[$idxSubLine])) {
                    $subLine = trim((string) $row[$idxSubLine]);
                    if ($subLine === '') {
                        $subLine = null;
                    }
                }

                if ($lineGroup === 'LINE_TEH') {
                    // kalau line teh, sub_line wajib & harus valid
                    if (!$subLine || !in_array($subLine, $validSubLines, true)) {
                        $this->skippedCount++;
                        $this->importErrors[] =
                            "Baris " . ($i + 1) .
                            ": sub_line wajib dan harus valid untuk LINE_TEH.";
                        continue;
                    }
                } else {
                    // non line teh → sub_line diabaikan
                    $subLine = null;
                }

                // shift (opsional)
                $shift = null;
                if ($idxShift !== null && isset($row[$idxShift]) && trim((string) $row[$idxShift]) !== '') {
                    $shiftVal = (int) $row[$idxShift];
                    $shift = ($shiftVal >= 1 && $shiftVal <= 3) ? $shiftVal : null;
                }

                // numeric helpers
                $cupWeight = $this->castNumeric($idxCupWeight !== null ? ($row[$idxCupWeight] ?? null) : null);
                $prodWeight = $this->castNumeric($idxProdWeight !== null ? ($row[$idxProdWeight] ?? null) : null);
                $weigh1 = $this->castNumeric($idxWeigh1 !== null ? ($row[$idxWeigh1] ?? null) : null);
                $weigh2 = $this->castNumeric($idxWeigh2 !== null ? ($row[$idxWeigh2] ?? null) : null);

                $notes = $idxNotes !== null ? (string) ($row[$idxNotes] ?? '') : null;

                // hitung total & moisture
                $totalCupPlusProd = null;
                $avgWeightG = null;
                $avgMoisturePercent = null;

                // hanya hitung otomatis untuk line tertentu
                if (in_array($lineGroup, $this->moistureLines, true)) {
                    if ($cupWeight !== null && $prodWeight !== null) {
                        $totalCupPlusProd = round($cupWeight + $prodWeight, 3);
                        $avgWeightG = $prodWeight;
                    }

                    if (
                        $totalCupPlusProd !== null &&
                        $weigh1 !== null &&
                        $weigh2 !== null &&
                        $prodWeight !== null &&
                        $prodWeight > 0
                    ) {
                        $avgWeighing = ($weigh1 + $weigh2) / 2;
                        $moisture = (
                            ($totalCupPlusProd - $avgWeighing) / $prodWeight
                        ) * 100;

                        $avgMoisturePercent = round($moisture, 2);
                    }
                }

                $payload = [
                    'line_group'           => $lineGroup,
                    'sub_line'             => $subLine,
                    'test_date'            => $testDate,
                    'product_name'         => $productName,
                    'shift'                => $shift,
                    'avg_moisture_percent' => $avgMoisturePercent,
                    'avg_weight_g'         => $avgWeightG,
                    'cup_weight'           => $cupWeight,
                    'product_weight'       => $prodWeight,
                    'total_cup_plus_product' => $totalCupPlusProd,
                    'weighing_1'           => $weigh1,
                    'weighing_2'           => $weigh2,
                    'notes'                => $notes,
                    'created_by'           => auth()->id(),
                ];

                IpcProductCheck::create($payload);

                $this->importedCount++;
            } catch (\Throwable $e) {
                $this->skippedCount++;
                $this->importErrors[] =
                    "Baris " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        Storage::disk('local')->delete($path);

        $this->showSuccessToast("Import IPC selesai. Berhasil: {$this->importedCount}, dilewati: {$this->skippedCount}.");

        // refresh list di komponen lain
        $this->dispatch('ipc:product_check_saved');

        $this->closeModal();
    }

    /**
     * Parse tanggal dari Excel (string atau numeric)
     */
    protected function parseExcelDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                )->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Konversi ke float (nullable)
     */
    protected function castNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        // kalau string dengan koma, ganti jadi titik
        $v = str_replace(',', '.', (string) $value);
        return is_numeric($v) ? (float) $v : null;
    }

    public function render()
    {
        return view('livewire.ipc.ipc-product-import');
    }
}
