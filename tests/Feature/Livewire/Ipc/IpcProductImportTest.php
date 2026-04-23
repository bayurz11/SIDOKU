<?php

namespace Tests\Feature\Livewire\Ipc;

use App\Livewire\Ipc\IpcProductImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Feature\Livewire\Ipc\Concerns\BuildsIpcUsers;
use Tests\TestCase;

class IpcProductImportTest extends TestCase
{
    use BuildsIpcUsers;
    use RefreshDatabase;

    public function test_it_imports_ipc_rows_in_chunks_and_skips_invalid_rows(): void
    {
        $user = $this->createUserWithPermissions(['ipc_moisture.create']);

        $this->actingAs($user);

        $rows = [];

        for ($index = 1; $index <= 205; $index++) {
            $rows[] = [
                'line_group' => 'LINE_TEH',
                'sub_line' => 'TEH_ORI',
                'test_date' => '2026-04-10',
                'product_name' => "Produk Teh {$index}",
                'shift' => ($index % 3) + 1,
                'cup_weight' => 10,
                'product_weight' => 5,
                'weighing_1' => 14.3,
                'weighing_2' => 14.1,
                'notes' => 'Imported',
            ];
        }

        $rows[] = [
            'line_group' => 'UNKNOWN',
            'sub_line' => 'TEH_ORI',
            'test_date' => '2026-04-10',
            'product_name' => 'Invalid Row',
            'shift' => 1,
            'cup_weight' => 10,
            'product_weight' => 5,
            'weighing_1' => 14.3,
            'weighing_2' => 14.1,
            'notes' => 'Should skip',
        ];

        $upload = $this->makeSpreadsheetUpload($rows);

        $component = Livewire::test(IpcProductImport::class)
            ->call('openModal')
            ->set('excel_file', $upload)
            ->call('import');

        $this->assertSame([], $component->errors()->toArray(), json_encode([
            'errors' => $component->errors()->toArray(),
            'import_errors' => $component->get('importErrors'),
        ]));
        $component->assertSet('showModal', false);

        $this->assertDatabaseCount('ipc_product_checks', 205);
        $this->assertDatabaseHas('ipc_product_checks', [
            'product_name' => 'Produk Teh 205',
            'created_by' => $user->id,
            'sub_line' => 'TEH_ORI',
        ]);
    }

    protected function makeSpreadsheetUpload(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'line_group',
            'sub_line',
            'test_date',
            'product_name',
            'shift',
            'cup_weight',
            'product_weight',
            'weighing_1',
            'weighing_2',
            'notes',
        ];

        foreach ($headers as $columnIndex => $header) {
            $sheet->setCellValueByColumnAndRow($columnIndex + 1, 1, $header);
        }

        foreach ($rows as $rowIndex => $row) {
            foreach ($headers as $columnIndex => $header) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 2, $row[$header] ?? null);
            }
        }

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ipc-import-'.uniqid().'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $upload = UploadedFile::fake()
            ->createWithContent('ipc-import.xlsx', file_get_contents($path))
            ->mimeType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        @unlink($path);

        return $upload;
    }
}
