<?php

namespace App\Domains\Ipc\Services;

use App\Domains\Ipc\Models\IpcProductCheck;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IpcProductCheckImportService
{
    public function prepareRows(Collection $rows, int $startRow, ?int $createdBy = null): array
    {
        $payloads = [];
        $errors = [];
        $skipped = 0;

        foreach ($rows->values() as $index => $row) {
            $result = $this->prepareRow((array) $row, $startRow + $index, $createdBy);

            if ($result['payload'] !== null) {
                $payloads[] = $result['payload'];

                continue;
            }

            $skipped++;
            $errors[] = $result['error'];
        }

        return [
            'payloads' => $payloads,
            'errors' => $errors,
            'skipped' => $skipped,
        ];
    }

    public function prepareRow(array $row, int $rowNumber, ?int $createdBy = null): array
    {
        $lineGroup = strtoupper(trim((string) ($row['line_group'] ?? '')));
        $testDateRaw = $row['test_date'] ?? null;
        $productName = trim((string) ($row['product_name'] ?? ''));
        $subLine = $this->normalizeNullableString($row['sub_line'] ?? null);

        if ($lineGroup === '' || ! in_array($lineGroup, array_keys(IpcProductCheck::LINE_GROUPS), true)) {
            return $this->invalidRow($rowNumber, "line_group '{$lineGroup}' tidak valid / tidak dikenal.");
        }

        $testDate = $this->parseExcelDate($testDateRaw);

        if (! $testDate) {
            return $this->invalidRow($rowNumber, 'test_date tidak valid.');
        }

        if ($productName === '') {
            return $this->invalidRow($rowNumber, 'product_name kosong, di-skip.');
        }

        if ($lineGroup === 'LINE_TEH') {
            if (! $subLine || ! in_array($subLine, array_keys(IpcProductCheck::SUB_LINES_TEH), true)) {
                return $this->invalidRow($rowNumber, 'sub_line wajib dan harus valid untuk LINE_TEH.');
            }
        } else {
            $subLine = null;
        }

        $shift = $this->normalizeShift($row['shift'] ?? null);
        $cupWeight = $this->castNumeric($row['cup_weight'] ?? null);
        $productWeight = $this->castNumeric($row['product_weight'] ?? null);
        $weighing1 = $this->castNumeric($row['weighing_1'] ?? null);
        $weighing2 = $this->castNumeric($row['weighing_2'] ?? null);

        [$totalCupPlusProduct, $avgWeight, $avgMoisturePercent] = $this->calculateMoistureMetrics(
            $lineGroup,
            $cupWeight,
            $productWeight,
            $weighing1,
            $weighing2
        );

        $timestamp = now();

        return [
            'payload' => [
                'line_group' => $lineGroup,
                'sub_line' => $subLine,
                'test_date' => $testDate,
                'product_name' => $productName,
                'shift' => $shift,
                'avg_moisture_percent' => $avgMoisturePercent,
                'avg_weight_g' => $avgWeight,
                'cup_weight' => $cupWeight,
                'product_weight' => $productWeight,
                'total_cup_plus_product' => $totalCupPlusProduct,
                'weighing_1' => $weighing1,
                'weighing_2' => $weighing2,
                'notes' => $this->normalizeNullableString($row['notes'] ?? null),
                'created_by' => $createdBy,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            'error' => null,
        ];
    }

    protected function calculateMoistureMetrics(
        string $lineGroup,
        ?float $cupWeight,
        ?float $productWeight,
        ?float $weighing1,
        ?float $weighing2
    ): array {
        if (! in_array($lineGroup, ['LINE_TEH', 'LINE_POWDER'], true)) {
            return [null, null, null];
        }

        $totalCupPlusProduct = null;
        $avgWeight = null;
        $avgMoisturePercent = null;

        if ($cupWeight !== null && $productWeight !== null) {
            $totalCupPlusProduct = round($cupWeight + $productWeight, 3);
            $avgWeight = $productWeight;
        }

        if (
            $totalCupPlusProduct !== null &&
            $weighing1 !== null &&
            $weighing2 !== null &&
            $productWeight !== null &&
            $productWeight > 0
        ) {
            $avgWeighing = ($weighing1 + $weighing2) / 2;
            $avgMoisturePercent = round((($totalCupPlusProduct - $avgWeighing) / $productWeight) * 100, 2);
        }

        return [$totalCupPlusProduct, $avgWeight, $avgMoisturePercent];
    }

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
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function castNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace(',', '.', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    protected function normalizeShift($value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $shift = (int) $value;

        return $shift >= 1 && $shift <= 3 ? $shift : null;
    }

    protected function normalizeNullableString($value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    protected function invalidRow(int $rowNumber, string $message): array
    {
        return [
            'payload' => null,
            'error' => "Baris {$rowNumber}: {$message}",
        ];
    }
}
