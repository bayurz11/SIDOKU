<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incoming_material_stage2_checks')) {
            Schema::drop('incoming_material_stage2_checks');
        }

        Schema::create('incoming_material_stage2_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incoming_material_id');
            $table->unsignedBigInteger('incoming_material_item_id')->nullable();
            $table->json('field_results')->nullable();
            $table->string('physical_result')->default('PENDING');
            $table->string('microbiology_result')->default('NOT_REQUIRED');
            $table->string('final_decision')->default('HOLD');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->unique('incoming_material_id', 'im_stage2_incoming_unique');
            $table->index('physical_result', 'im_stage2_physical_idx');
            $table->index('microbiology_result', 'im_stage2_micro_idx');
            $table->index('final_decision', 'im_stage2_decision_idx');
            $table->foreign('incoming_material_id', 'im_stage2_incoming_fk')
                ->references('id')
                ->on('incoming_materials')
                ->cascadeOnDelete();
            $table->foreign('incoming_material_item_id', 'im_stage2_item_fk')
                ->references('id')
                ->on('incoming_material_items')
                ->nullOnDelete();
            $table->foreign('checked_by', 'im_stage2_checked_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_material_stage2_checks');
    }
};
