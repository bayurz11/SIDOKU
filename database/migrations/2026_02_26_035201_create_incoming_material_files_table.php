<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_material_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incoming_material_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('file_name');      // nama asli
            $table->string('file_path');      // path di storage
            $table->string('file_type');      // pdf, jpg, png
            $table->string('category')->nullable(); // photo / document / coa

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_material_files');
    }
};
