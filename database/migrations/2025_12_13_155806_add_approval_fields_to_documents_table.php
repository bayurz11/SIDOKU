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
        // 1) Tambah kolom-kolom dulu
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('documents', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_at');
            }

            if (!Schema::hasColumn('documents', 'current_approval_request_id')) {
                $table->unsignedBigInteger('current_approval_request_id')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('documents', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('is_active');
            }
        });

        // 2) Tambah foreign key setelah kolom ada
        Schema::table('documents', function (Blueprint $table) {
            // Nama constraint custom supaya gampang di-drop dan aman
            $fkName = 'documents_current_approval_request_fk';

            // Catatan: Laravel tidak punya hasForeignKey bawaan.
            // Diasumsikan migration ini belum pernah jalan sebelumnya.
            $table->foreign('current_approval_request_id', $fkName)
                ->references('id')
                ->on('document_approval_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'current_approval_request_id')) {
                // drop FK dulu
                $table->dropForeign('documents_current_approval_request_fk');
                $table->dropColumn('current_approval_request_id');
            }

            if (Schema::hasColumn('documents', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('documents', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }

            if (Schema::hasColumn('documents', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });
    }
};
