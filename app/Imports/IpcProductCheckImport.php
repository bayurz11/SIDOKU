<?php

namespace App\Imports;

use App\Domains\Ipc\Models\IpcProductCheck;
use App\Domains\Ipc\Services\IpcProductCheckImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class IpcProductCheckImport implements SkipsEmptyRows, ToCollection, WithChunkReading
{
    use Importable;
    use RemembersChunkOffset;

    public const CHUNK_SIZE = 200;

    protected int $importedCount = 0;

    protected int $skippedCount = 0;

    protected array $errors = [];

    protected ?array $headers = null;

    public function __construct(
        protected IpcProductCheckImportService $service,
        protected ?int $createdBy = null
    ) {}

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $chunkOffset = $this->getChunkOffset() ?? 0;

        if ($this->headers === null) {
            $this->headers = $this->normalizeHeaders($this->extractRow($rows->first()));
            $rows = $rows->slice(1)->values();

            $missingHeaders = array_diff(['line_group', 'test_date', 'product_name'], $this->headers);

            if ($missingHeaders !== []) {
                $this->errors[] = 'Header minimal line_group, test_date, dan product_name wajib ada di file Excel.';
                $this->skippedCount += $rows->count();

                return;
            }

            $startRow = 2;
        } else {
            $startRow = $chunkOffset + 1;
        }

        $mappedRows = $rows
            ->map(fn ($row) => $this->mapRow($this->extractRow($row)))
            ->filter(fn (array $row) => collect($row)->filter(
                fn ($value) => trim((string) $value) !== ''
            )->isNotEmpty())
            ->values();

        if ($mappedRows->isEmpty()) {
            return;
        }

        $prepared = $this->service->prepareRows($mappedRows, $startRow, $this->createdBy);

        if ($prepared['payloads'] !== []) {
            DB::transaction(function () use ($prepared) {
                IpcProductCheck::query()->insert($prepared['payloads']);
            });
        }

        $this->importedCount += count($prepared['payloads']);
        $this->skippedCount += $prepared['skipped'];
        $this->errors = [...$this->errors, ...$prepared['errors']];
    }

    public function chunkSize(): int
    {
        return self::CHUNK_SIZE;
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }

    public function skippedCount(): int
    {
        return $this->skippedCount;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    protected function normalizeHeaders(array $headers): array
    {
        return array_map(
            fn ($header) => strtolower(trim((string) $header)),
            $headers
        );
    }

    protected function mapRow(array $row): array
    {
        $row = array_values($row);
        $row = array_pad($row, count($this->headers ?? []), null);

        return array_combine($this->headers ?? [], array_slice($row, 0, count($this->headers ?? []))) ?: [];
    }

    protected function extractRow(mixed $row): array
    {
        if ($row instanceof Collection) {
            return $row->toArray();
        }

        return (array) $row;
    }
}
