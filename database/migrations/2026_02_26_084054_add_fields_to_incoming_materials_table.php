<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incoming_materials', function (Blueprint $table) {

            $table->time('receipt_time')->nullable()->after('date');
            $table->string('quantity_unit')->nullable()->after('quantity');
            $table->decimal('sample_quantity', 10, 2)->nullable()->after('quantity_unit');
            $table->string('vehicle_number', 50)->nullable()->after('sample_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('incoming_materials', function (Blueprint $table) {

            $table->dropColumn([
                'receipt_time',
                'quantity_unit',
                'sample_quantity',
                'vehicle_number',
            ]);
        });
    }
};
