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
        Schema::create('ipc_check_product', function (Blueprint $table) {
            $table->id();

            // Identitas line & sub-line
            $table->enum('line_group', [
                'LINE_TEH',
                'LINE_POWDER',
                'LINE_MINUMAN_BERPERISA',
                'LINE_AMDK',
                'LINE_CONDIMENTS',
            ]);

            $table->enum('sub_line', [
                'TEH_ORI',
                'TEH_SACHET',
                'TEH_SEDUH_50G',
                'TEH_SEDUH_100G',
                'TEH_BUBUK_1KG',
                'TEH_AMPLOP',
                'TEH_HIJAU',
            ])->nullable(); // khusus LINE_TEH

            // Data utama
            $table->date('test_date');                // Hari, Tanggal
            $table->string('product_name', 150);      // Nama Produk
            $table->tinyInteger('shift')->nullable(); // Shift (jika digunakan)

            // Parameter hasil uji
            $table->decimal('avg_weight_g', 8, 3)->nullable();      // Berat (g)
            $table->decimal('avg_ph', 5, 2)->nullable();            // pH
            $table->decimal('avg_brix', 5, 2)->nullable();          // Brix
            $table->decimal('avg_tds_ppm', 8, 2)->nullable();       // TDS (ppm)

            $table->decimal('avg_chlorine', 8, 3)->nullable();      // Klorin (AMDK)
            $table->decimal('avg_ozone', 8, 3)->nullable();         // Ozon (AMDK)
            $table->decimal('avg_turbidity_ntu', 8, 3)->nullable(); // Kekeruhan (NTU)

            $table->decimal('avg_salinity', 8, 3)->nullable();      // Salinitas (Condiments)

            // Catatan tambahan (opsional)
            $table->text('notes')->nullable();

            // ✅ USER PENGINPUT
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Index untuk performa
            $table->index(['line_group', 'sub_line']);
            $table->index('test_date');
            $table->index('product_name');
            $table->index('created_by');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipc_check_product');
    }
};
