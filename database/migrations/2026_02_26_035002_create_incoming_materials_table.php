<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_materials', function (Blueprint $table) {
            $table->id();

            // Informasi utama
            $table->date('date');
            $table->string('supplier');
            $table->string('material_name');
            $table->string('batch_number')->index();
            $table->integer('quantity');

            // Status inspeksi
            $table->enum('status', ['ACCEPTED', 'HOLD', 'REJECTED'])
                ->default('HOLD')
                ->index();

            // Catatan tambahan
            $table->text('notes')->nullable();

            // Audit trail
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->foreignId('updated_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_materials');
    }
};
