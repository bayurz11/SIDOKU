<?php

namespace App\Domains\Document\Services;

use App\Domains\Document\Models\DocumentPrefixSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * DocumentNumberService
 * -----------------------------------------
 * Digunakan untuk menghasilkan nomor dokumen otomatis
 * sesuai format prefix di tabel DocumentPrefixSetting.
 */
class DocumentNumberService
{
    /**
     * Generate nomor dokumen otomatis.
     *
     * @param string $departmentCode - Kode departemen (contoh: QC, QS)
     * @param string $mainType - Kode jenis dokumen (contoh: SOP, DOC, WI)
     * @param string|null $subRef - Sub referensi (contoh: QMS, SOP001)
     * @return string - Nomor dokumen hasil generate
     */
    public static function generate(string $departmentCode, string $mainType, ?string $subRef = null): string
    {
        try {
            // Ambil format aktif sesuai jenis dokumen
            $setting = DocumentPrefixSetting::query()
                ->where('is_active', true)
                ->whereHas('documentType', fn($q) => $q->where('kode', $mainType))
                ->first();

            if (!$setting) {
                Log::warning("DocumentPrefixSetting tidak ditemukan untuk tipe: {$mainType}");
                return "PRP/{$mainType}/{$departmentCode}/001";
            }

            // Reset sequence jika tahun/bulan baru
            if ($setting->reset_interval === 1 && date('Y') != $setting->updated_at?->format('Y')) {
                $setting->last_sequence = 0;
            } elseif ($setting->reset_interval === 2 && date('Ym') != $setting->updated_at?->format('Ym')) {
                $setting->last_sequence = 0;
            }

            // Increment nomor urut
            $setting->last_sequence += 1;
            $setting->save();

            // Placeholder & replacement
            $replacements = [
                '{{COMP}}'  => $setting->company_prefix ?? 'PRP',
                '{{MAIN}}'  => strtoupper($mainType),
                '{{SUB}}'   => $subRef ?? '',
                '{{DEPT}}'  => strtoupper($departmentCode),
                '{{SEQ}}'   => str_pad($setting->last_sequence, 3, '0', STR_PAD_LEFT),
                '{{YEAR}}'  => date('Y'),
                '{{MONTH}}' => date('m'),
            ];

            $output = $setting->format_nomor;
            foreach ($replacements as $key => $val) {
                $output = Str::replace($key, $val, $output);
            }

            $output = preg_replace('/\s+/', ' ', trim($output));

            // Update contoh output terakhir
            $setting->example_output = $output;
            $setting->saveQuietly();

            return $output;
        } catch (\Throwable $e) {
            Log::error('Gagal generate nomor dokumen: ' . $e->getMessage());
            return 'ERROR/NOMOR';
        }
    }
}
