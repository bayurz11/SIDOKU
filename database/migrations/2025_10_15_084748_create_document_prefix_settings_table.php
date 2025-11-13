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
        Schema::create('document_prefix_settings', function (Blueprint $table) {
            $table->id();

            // Default prefix perusahaan atau proyek (mis: PRP)
            $table->string('company_prefix', 20)->default('PRP');

            // Jenis utama dokumen (SOP, WI, FORM, DOC)
            $table->foreignId('document_type_id')->nullable()->constrained('document_types')->nullOnDelete();

            // Subkode atau referensi ke dokumen lain (misal SOP001, WI.SOP001.002)
            $table->string('sub_reference_format', 100)->nullable()
                ->comment('Format sub referensi, mis: SOP{{SEQ}} atau WI.SOP{{SEQ}}.{{SUBSEQ}}');

            // Departemen yang punya dokumen
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            // Template format nomor penuh
            $table->string('format_nomor', 150)
                ->default('{{COMP}}/{{MAIN}}/{{DEPT}}/{{SEQ}}')
                ->comment('Format umum, misal {{COMP}}/{{MAIN}}.{{SUB}}/{{DEPT}}/{{SEQ}}');

            $table->integer('last_sequence')->default(0);
            $table->integer('last_subsequence')->default(0);

            $table->integer('reset_interval')->default(1)
                ->comment('0=never, 1=yearly, 2=monthly');

            $table->string('example_output', 200)->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_prefix_settings');
    }
};
