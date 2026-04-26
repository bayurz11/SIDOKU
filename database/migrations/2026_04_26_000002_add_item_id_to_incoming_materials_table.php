<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incoming_materials', function (Blueprint $table) {
            $table->foreignId('incoming_material_item_id')
                ->nullable()
                ->after('material_name')
                ->constrained('incoming_material_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incoming_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('incoming_material_item_id');
        });
    }
};
