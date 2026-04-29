<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $items = [
            [
                'name' => 'Bahan Baku Teh',
                'category' => 'tea',
                'default_unit' => 'KG',
                'requires_microbiology' => true,
                'stage2_fields' => json_encode([
                    'Kondisi kemasan',
                    'Warna daun teh',
                    'Aroma',
                    'Benda asing',
                    'Kesesuaian COA',
                ]),
                'description' => 'Template awal bahan baku teh dengan uji mikrobiologi incoming material.',
            ],
            [
                'name' => 'Bahan Baku Umum',
                'category' => 'general',
                'default_unit' => 'KG',
                'requires_microbiology' => false,
                'stage2_fields' => json_encode([
                    'Kondisi kemasan',
                    'Kebersihan material',
                    'Kesesuaian label',
                    'Kesesuaian COA',
                ]),
                'description' => 'Template awal bahan baku umum.',
            ],
        ];

        foreach ($items as $item) {
            DB::table('incoming_material_items')->updateOrInsert(
                ['name' => $item['name']],
                [
                    ...$item,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('incoming_material_items')
            ->whereIn('name', ['Bahan Baku Teh', 'Bahan Baku Umum'])
            ->delete();
    }
};
