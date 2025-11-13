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
        Schema::table('document_prefix_settings', function (Blueprint $table) {
            $table->tinyInteger('level')
                ->default(1)
                ->comment('1=DOC, 2=SOP, 3=WI, 4=FORM, dst')
                ->after('document_type_id');

            $table->foreignId('parent_document_type_id')
                ->nullable()
                ->constrained('document_types')
                ->nullOnDelete()
                ->after('level');

            $table->string('parent_reference_format', 150)
                ->nullable()
                ->comment('Contoh: SOP{{PARENT_SEQ}} atau WI.SOP{{PARENT_SEQ}}.{{SEQ}}')
                ->after('parent_document_type_id');

            $table->string('sub_reference_format', 150)
                ->nullable()
                ->comment('Format sub referensi tambahan, mis: FORM.WI.{{PARENT_REF}}.{{SEQ}}')
                ->change();

            $table->string('format_nomor', 200)
                ->default('{{COMP}}/{{MAIN}}{{PARENT_SEG}}/{{DEPT}}/{{SEQ}}')
                ->comment('Gunakan placeholder: {{COMP}}, {{MAIN}}, {{PARENT_REF}}, {{DEPT}}, {{SEQ}}, {{SUBSEQ}}')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('document_prefix_settings', function (Blueprint $table) {
            // Di sini kembalikan ke kondisi sebelumnya (opsional)
        });
    }
};
