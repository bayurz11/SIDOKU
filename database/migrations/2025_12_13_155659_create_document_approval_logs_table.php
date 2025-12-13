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
        Schema::create('document_approval_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('approval_request_id')
                ->constrained('document_approval_requests')
                ->cascadeOnDelete();

            $table->foreignId('approval_step_id')
                ->constrained('document_approval_steps')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // approved | rejected
            $table->string('action', 20);

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            // optional: nama device (kalau kamu mau isi dari client)
            $table->string('device_name', 120)->nullable();

            $table->timestamp('signed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'action']);
            $table->index(['approval_request_id', 'signed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_approval_logs');
    }
};
