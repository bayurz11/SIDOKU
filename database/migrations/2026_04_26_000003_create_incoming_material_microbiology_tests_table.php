<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incoming_material_microbiology_tests')) {
            Schema::drop('incoming_material_microbiology_tests');
        }

        Schema::create('incoming_material_microbiology_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incoming_material_id');
            $table->decimal('tpc', 12, 2)->nullable();
            $table->decimal('yeast_mold', 12, 2)->nullable();
            $table->decimal('coliform', 12, 2)->nullable();
            $table->string('e_coli')->nullable();
            $table->string('salmonella')->nullable();
            $table->string('result')->default('PENDING');
            $table->string('status')->default('DRAFT');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('tested_by')->nullable();
            $table->timestamp('tested_at')->nullable();
            $table->timestamps();

            $table->unique('incoming_material_id', 'im_micro_incoming_unique');
            $table->index('result', 'im_micro_result_idx');
            $table->index('status', 'im_micro_status_idx');
            $table->foreign('incoming_material_id', 'im_micro_incoming_fk')
                ->references('id')
                ->on('incoming_materials')
                ->cascadeOnDelete();
            $table->foreign('tested_by', 'im_micro_tested_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_material_microbiology_tests');
    }
};
