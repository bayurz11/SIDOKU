<?php

namespace App\Domains\Document\Services;

use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentPrefixSetting;
use App\Domains\Document\Models\DocumentType;
use App\Domains\Department\Models\Department;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate nomor dokumen baru.
     *
     * @param  int|null       $documentTypeId
     * @param  int|null       $departmentId
     * @param  Document|null  $parentDocument
     * @return array ['code' => 'PRP/SOP/QC/001', 'prefix_setting_id' => 1, 'seq' => 1]
     */
    public static function generate(?int $documentTypeId, ?int $departmentId = null, ?Document $parentDocument = null): array
    {
        return DB::transaction(function () use ($documentTypeId, $departmentId, $parentDocument) {
            /** @var DocumentType|null $type */
            $type = $documentTypeId
                ? DocumentType::findOrFail($documentTypeId)
                : null;

            /** @var Department|null $dept */
            $dept = $departmentId
                ? Department::find($departmentId)
                : null;

            // Cari prefix setting yang cocok (sederhana: by document_type_id + department_id)
            $prefix = DocumentPrefixSetting::query()
                ->where('is_active', true)
                ->when($documentTypeId, fn($q) => $q->where('document_type_id', $documentTypeId))
                ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
                ->orderByDesc('id')
                ->firstOrFail();

            // TODO: tambahkan logika reset_interval berdasarkan tahun/bulan jika dibutuhkan

            // Hitung sequence baru
            $nextSeq = $prefix->last_sequence + 1;

            // Siapkan nilai placeholder
            $placeholders = [
                '{COMP}'       => $prefix->company_prefix ?? 'PRP',
                '{MAIN}'       => $type?->code ?? $type?->name ?? '', // sesuaikan: pakai field 'code' kalau ada
                '{DEPT}'       => $dept?->code ?? $dept?->name ?? '',  // sesuaikan: pakai field 'code' kalau ada
                '{SEQ}'        => str_pad($nextSeq, 3, '0', STR_PAD_LEFT),
                '{SUBSEQ}'     => '001', // sementara default, bisa dikembangkan
                '{PARENT_REF}' => static::buildParentRef($parentDocument),
            ];

            // Bangun kode akhir dari format
            $format = $prefix->format_nomor; // contoh: {COMP}/{MAIN}/{DEPT}/{SEQ}
            $documentCode = strtr($format, $placeholders);

            // Update sequence di prefix
            $prefix->update([
                'last_sequence' => $nextSeq,
            ]);

            return [
                'code'               => $documentCode,
                'prefix_setting_id'  => $prefix->id,
                'sequence'           => $nextSeq,
            ];
        });
    }

    /**
     * Bangun PARENT_REF dari dokumen induk.
     * Contoh:
     *  - SOP induk:  PRP/SOP/QC/001 -> SOP001
     *  - WI induk:   PRP/WI.SOP001/QS/002 -> WI.SOP001.002 (tergantung pola yang kamu tentukan)
     */
    protected static function buildParentRef(?Document $parent): ?string
    {
        if (!$parent) {
            return null;
        }

        // Versi sederhana:
        // Ambil dari document_code & ambil bagian belakangnya
        // Misal: PRP/SOP/QC/001 -> SOP001
        $code = $parent->document_code;

        // Ini bisa kamu ganti dengan regex sesuai pola PRP mu
        // Untuk sementara, contoh sederhana:
        // 1. Split "/"
        $parts = explode('/', $code);

        // SOP biasanya ada di bagian kedua -> SOP atau WI.SOP001, dst
        // Nomor sequence di bagian terakhir
        if (count($parts) >= 3) {
            $main = $parts[1];                // SOP, WI.SOP001, dll
            $seq  = end($parts);              // 001
            return $main . $seq;              // SOP001, WI.SOP001001 (ini bisa kamu refine)
        }

        return $code; // fallback
    }
}
