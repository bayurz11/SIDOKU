<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_material_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->default('general')->index();
            $table->string('default_unit', 30)->nullable();
            $table->boolean('requires_microbiology')->default(false)->index();
            $table->json('stage2_fields')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_material_items');
    }
};
