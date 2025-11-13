<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nama jenis dokumen, misalnya SOP, WI, Form');
            $table->text('description')->nullable()->comment('Deskripsi singkat tentang jenis dokumen');
            $table->boolean('is_active')->default(true)->comment('Status aktif / nonaktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
