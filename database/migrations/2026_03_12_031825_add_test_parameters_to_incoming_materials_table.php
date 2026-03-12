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
        Schema::table('incoming_materials', function (Blueprint $table) {

            // Parameter pengujian
            $table->boolean('test_moisture')->default(false)->after('vehicle_number');
            $table->boolean('test_microbiology')->default(false)->after('test_moisture');
            $table->boolean('test_chemical')->default(false)->after('test_microbiology');

            // Status proses lab
            $table->string('lab_status')->nullable()->after('test_chemical');
        });
    }

    public function down(): void
    {
        Schema::table('incoming_materials', function (Blueprint $table) {

            $table->dropColumn([
                'test_moisture',
                'test_microbiology',
                'test_chemical',
                'lab_status'
            ]);
        });
    }
};
