<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_material_inspections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incoming_material_id')
                ->constrained('incoming_materials')
                ->cascadeOnDelete();

            $table->string('parameter')->nullable();
            $table->string('standard')->nullable();
            $table->string('test_result')->nullable();        // ok / not ok
            $table->string('inspection_result')->nullable();  // OK / NOT OK

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index('incoming_material_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_material_inspections');
    }
};
