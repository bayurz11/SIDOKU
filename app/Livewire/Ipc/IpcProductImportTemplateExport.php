<?php

namespace App\Livewire\Ipc;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IpcProductImportTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'line_group',      // wajib, key di IpcProductCheck::LINE_GROUPS (mis: LINE_TEH, LINE_POWDER)
            'sub_line',        // opsional, wajib & valid kalau line_group = LINE_TEH (key SUB_LINES_TEH)
            'test_date',       // wajib (Y-m-d atau bisa diubah operator)
            'product_name',    // wajib
            'shift',           // opsional (1–3)

            // kolom hitung kadar air (opsional, kalau diisi → sistem hitung otomatis)
            'cup_weight',      // berat cawan
            'product_weight',  // berat produk
            'weighing_1',      // penimbangan 1
            'weighing_2',      // penimbangan 2

            'notes',           // catatan (opsional)
        ];
    }

    public function array(): array
    {
        return [
            // Contoh 1: Line Teh dengan perhitungan kadar air
            [
                'LINE_TEH',          // line_group
                'TEH_A',             // sub_line (contoh key SUB_LINES_TEH)
                '2025-01-01',        // test_date
                'Teh Ori 200 ml',    // product_name
                1,                   // shift

                45.50,               // cup_weight
                5.00,                // product_weight
                50.10,               // weighing_1
                50.05,               // weighing_2

                'Contoh data IPC line teh', // notes
            ],

            // Contoh 2: Line Powder tanpa sub_line
            [
                'LINE_POWDER',       // line_group
                '',                  // sub_line (kosong karena bukan LINE_TEH)
                '2025-01-01',        // test_date
                'Instant Powder 25 g', // product_name
                2,                   // shift

                32.00,               // cup_weight
                3.50,                // product_weight
                35.40,               // weighing_1
                35.35,               // weighing_2

                'Contoh data IPC line powder', // notes
            ],
        ];
    }
}
