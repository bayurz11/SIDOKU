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
        Schema::create('document_approval_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('approval_request_id')
                ->constrained('document_approval_requests')
                ->cascadeOnDelete();

            // 1,2,3,...
            $table->unsignedTinyInteger('step_order');

            $table->foreignId('approver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // pending | approved | rejected | skipped
            $table->string('status', 20)->default('pending');

            $table->timestamp('acted_at')->nullable(); // kapan approve/reject
            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique(['approval_request_id', 'step_order']);
            $table->index(['approver_id', 'status']);
            $table->index(['approval_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_approval_steps');
    }
};
