<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DocumentImportTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'document_code',   // optional
            'title',           // wajib
            'document_type',   // nama di Master DocumentType
            'department',      // nama di Master Department
            'level',
            'status',          // draft / in_review / approved / obsolete
            'effective_date',  // Y-m-d
            'expired_date',    // optional
            'summary',         // optional
        ];
    }

    public function array(): array
    {
        return [
            [
                '',                               // document_code (kosong → auto generate)
                'Contoh SOP Pengemasan Produk',   // title
                'SOP',                            // document_type
                'Produksi',                       // department
                1,                                // level
                'draft',                          // status
                '2025-01-01',                     // effective_date
                '',                               // expired_date
                'Contoh ringkasan dokumen',       // summary
            ],
        ];
    }
}
