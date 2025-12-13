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
        Schema::create('document_revisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_id')
                ->constrained('documents')
                ->cascadeOnDelete();

            $table->unsignedInteger('revision_no')->default(0);
            $table->text('change_note')->nullable();
            $table->string('file_path', 255)->nullable();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('changed_at')->nullable();

            $table->timestamps();

            $table->unique(['document_id', 'revision_no']);
            $table->index(['document_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_revisions');
    }
};
