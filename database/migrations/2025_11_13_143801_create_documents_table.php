<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Jenis dokumen: DOC, SOP, WI, FORM, dll (FK ke document_types)
            $table->foreignId('document_type_id')
                ->constrained('document_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Departemen pemilik dokumen (QS, QC, PRD, dll)
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            // Prefix setting yang dipakai saat generate nomor
            $table->foreignId('document_prefix_setting_id')
                ->nullable()
                ->constrained('document_prefix_settings')
                ->nullOnDelete();

            // Dokumen induk (mis: SOP induk dari WI, WI induk dari FORM)
            $table->foreignId('parent_document_id')
                ->nullable()
                ->constrained('documents')
                ->nullOnDelete();

            // Kode dokumen final (hasil generate), mis: PRP/SOP/QC/001
            $table->string('document_code', 200)->unique();

            // Judul dokumen
            $table->string('title', 255);

            // Level dokumen (opsional tapi enak untuk filter)
            // 1=DOC, 2=SOP, 3=WI, 4=FORM, dll
            $table->tinyInteger('level')->default(1);

            // Nomor revisi (0 = initial)
            $table->integer('revision_no')->default(0);

            // Status lifecycle dokumen
            // draft, in_review, approved, obsolete
            $table->string('status', 20)->default('draft');

            // Tanggal efektif & kadaluarsa (opsional)
            $table->date('effective_date')->nullable();
            $table->date('expired_date')->nullable();

            // Lokasi file di storage (PDF, dll)
            $table->string('file_path', 255)->nullable();

            // Keterangan singkat (opsional)
            $table->text('summary')->nullable();

            // Flag aktif / obsolete
            $table->boolean('is_active')->default(true);

            // Audit trail user
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
