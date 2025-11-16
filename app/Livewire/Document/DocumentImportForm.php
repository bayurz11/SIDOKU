<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Shared\Traits\WithAlerts;
use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentType;
use App\Domains\Department\Models\Department;
use App\Domains\Document\Services\DocumentNumberService;
use App\Shared\Services\LoggerService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel; // pastikan package maatwebsite/excel sudah terinstall
use Carbon\Carbon;

class DocumentImportForm extends Component
{
    use WithFileUploads, WithAlerts;

    public bool $showModal = false;

    // file excel yang diupload
    public $excel_file;

    // statistik sederhana
    public int $importedCount = 0;
    public int $skippedCount  = 0;
    public array $errors      = [];

    protected $listeners = [
        'openDocumentImportForm' => 'openModal',
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
        $this->errors         = [];
    }

    public function closeModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->reset([
            'excel_file',
            'importedCount',
            'skippedCount',
            'errors',
            'showModal',
        ]);

        $this->showModal = false;
    }

    /**
     * Import data dari Excel
     *
     * Asumsi header kolom di sheet pertama:
     *  - document_code      (optional, kalau kosong akan digenerate otomatis)
     *  - title              (wajib)
     *  - document_type      (nama DocumentType, misal: "SOP", "WI", "Manual Keamanan Pangan")
     *  - department         (nama Department, misal: "Quality System", "QC", "HR")
     *  - level              (angka: 1,2,3,4...)
     *  - status             (draft, in_review, approved, obsolete)
     *  - effective_date     (format: Y-m-d atau Excel date)
     *  - expired_date       (optional)
     *  - summary            (optional)
     */
    public function import(): void
    {
        $this->validate();

        // simpan file sementara
        $path = $this->excel_file->store('temp/imports', 'local');
        $fullPath = storage_path('app/' . $path);

        $this->importedCount = 0;
        $this->skippedCount  = 0;
        $this->errors        = [];

        // baca excel menggunakan maatwebsite/excel
        $sheets = Excel::toArray([], $fullPath);
        $rows   = $sheets[0] ?? [];

        if (count($rows) <= 1) {
            $this->addError('excel_file', 'File Excel kosong atau tidak memiliki data.');
            return;
        }

        // anggap baris pertama adalah header
        $headerRow = array_map(function ($v) {
            return strtolower(trim((string) $v));
        }, $rows[0]);

        // fungsi bantu ambil index kolom
        $col = function (string $name) use ($headerRow): ?int {
            $idx = array_search(strtolower($name), $headerRow);
            return $idx === false ? null : $idx;
        };

        $idxDocumentCode = $col('document_code');
        $idxTitle        = $col('title');
        $idxDocType      = $col('document_type');
        $idxDept         = $col('department');
        $idxLevel        = $col('level');
        $idxStatus       = $col('status');
        $idxEffDate      = $col('effective_date');
        $idxExpDate      = $col('expired_date');
        $idxSummary      = $col('summary');

        // minimal: harus ada title dan document_type
        if (is_null($idxTitle) || is_null($idxDocType)) {
            $this->addError('excel_file', 'Kolom minimal "title" dan "document_type" harus ada di header.');
            return;
        }

        foreach ($rows as $i => $row) {
            // skip header row
            if ($i === 0) {
                continue;
            }

            // jika baris kosong, skip
            if (!array_filter($row, fn($v) => trim((string) $v) !== '')) {
                continue;
            }

            try {
                $title = trim((string) ($row[$idxTitle] ?? ''));

                if ($title === '') {
                    $this->skippedCount++;
                    $this->errors[] = "Baris " . ($i + 1) . ": title kosong, di-skip.";
                    continue;
                }

                $docTypeName = trim((string) ($row[$idxDocType] ?? ''));
                $deptName    = $idxDept !== null ? trim((string) ($row[$idxDept] ?? '')) : null;

                $documentType = DocumentType::where('name', $docTypeName)->first();
                if (!$documentType) {
                    $this->skippedCount++;
                    $this->errors[] = "Baris " . ($i + 1) . ": Document type '{$docTypeName}' tidak ditemukan.";
                    continue;
                }

                $department = null;
                if ($deptName) {
                    $department = Department::where('name', $deptName)->first();
                    if (!$department) {
                        $this->skippedCount++;
                        $this->errors[] = "Baris " . ($i + 1) . ": Department '{$deptName}' tidak ditemukan.";
                        continue;
                    }
                }

                // level
                $level = 1;
                if ($idxLevel !== null && isset($row[$idxLevel]) && trim((string) $row[$idxLevel]) !== '') {
                    $level = (int) $row[$idxLevel];
                }

                // status
                $status = 'draft';
                if ($idxStatus !== null && isset($row[$idxStatus]) && trim((string) $row[$idxStatus]) !== '') {
                    $s = strtolower(trim((string) $row[$idxStatus]));
                    $allowed = ['draft', 'in_review', 'approved', 'obsolete'];
                    $status = in_array($s, $allowed) ? $s : 'draft';
                }

                // tanggal efektif & expired
                $effectiveDate = null;
                $expiredDate   = null;

                if ($idxEffDate !== null && isset($row[$idxEffDate]) && trim((string) $row[$idxEffDate]) !== '') {
                    $effectiveDate = $this->parseExcelDate($row[$idxEffDate]);
                }

                if ($idxExpDate !== null && isset($row[$idxExpDate]) && trim((string) $row[$idxExpDate]) !== '') {
                    $expiredDate = $this->parseExcelDate($row[$idxExpDate]);
                }

                $summary = $idxSummary !== null ? (string) ($row[$idxSummary] ?? null) : null;

                // Document code: kalau kolom document_code kosong → generate
                $manualCode = $idxDocumentCode !== null ? trim((string) ($row[$idxDocumentCode] ?? '')) : '';

                if ($manualCode === '') {
                    // generate nomor berdasarkan prefix setting
                    $generated = DocumentNumberService::generate(
                        $documentType->id,
                        $department?->id,
                        null
                    );
                    $documentCode = $generated['code'];
                    $prefixId     = $generated['prefix_setting_id'] ?? null;
                } else {
                    $documentCode = $manualCode;
                    $prefixId     = null; // atau bisa dicari kalau mau strict
                }

                Document::create([
                    'document_type_id'           => $documentType->id,
                    'department_id'              => $department?->id,
                    'document_prefix_setting_id' => $prefixId,
                    'parent_document_id'         => null,
                    'document_code'              => $documentCode,
                    'title'                      => $title,
                    'summary'                    => $summary,
                    'effective_date'             => $effectiveDate,
                    'expired_date'               => $expiredDate,
                    'level'                      => $level,
                    'revision_no'                => 0,
                    'status'                     => $status,
                    'file_path'                  => null,
                    'is_active'                  => $status !== 'obsolete',
                    'created_by'                 => auth()->id(),
                ]);

                $this->importedCount++;
            } catch (\Throwable $e) {
                $this->skippedCount++;
                $this->errors[] = "Baris " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        // optional: hapus file temp
        Storage::disk('local')->delete($path);

        LoggerService::logUserAction('import', 'Document', null, [
            'imported' => $this->importedCount,
            'skipped'  => $this->skippedCount,
        ]);

        $this->showSuccessToast("Import selesai. Berhasil: {$this->importedCount}, dilewati: {$this->skippedCount}.");
        $this->dispatch('document:saved'); // supaya list refresh
    }

    /**
     * Parse value tanggal dari Excel (bisa string atau numeric date)
     */
    protected function parseExcelDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // kalau numeric (excel serialized date)
        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                )->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        // kalau string normal
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.document.document-import-form');
    }
}
