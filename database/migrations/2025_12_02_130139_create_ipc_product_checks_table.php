<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipc_product_checks', function (Blueprint $table) {
            $table->id();

            // Kelompok line besar
            $table->enum('line_group', [
                'LINE_TEH',
                'LINE_POWDER',
                'LINE_MINUMAN_BERPERISA',
                'LINE_AMDK',
                'LINE_CONDIMENTS',
            ]);

            // Sub-line (khusus line teh, lainnya boleh null)
            $table->enum('sub_line', [
                'TEH_ORI',
                'TEH_SACHET',
                'TEH_SEDUH_50G',
                'TEH_SEDUH_100G',
                'TEH_BUBUK_1KG',
                'TEH_AMPLOP',
                'TEH_HIJAU',
            ])->nullable();

            // Tanggal & produk
            $table->date('test_date');
            $table->string('product_name', 150);

            // Shift (kalau tidak dipakai, boleh null)
            $table->tinyInteger('shift')->nullable(); // 1 / 2 / 3

            // Nilai rata-rata (semua nullable supaya fleksibel)
            $table->decimal('avg_moisture_percent', 5, 2)->nullable(); // Kadar air %
            $table->decimal('avg_weight_g', 8, 3)->nullable();         // Berat g
            $table->decimal('avg_ph', 4, 2)->nullable();               // pH
            $table->decimal('avg_brix', 5, 2)->nullable();             // °Brix
            $table->decimal('avg_tds_ppm', 8, 2)->nullable();          // TDS ppm
            $table->decimal('avg_chlorine', 6, 3)->nullable();         // Klorin
            $table->decimal('avg_ozone', 6, 3)->nullable();            // Ozon
            $table->decimal('avg_turbidity_ntu', 6, 3)->nullable();    // NTU
            $table->decimal('avg_salinity', 6, 3)->nullable();         // Salinitas

            $table->text('notes')->nullable();

            // Optional: relasi ke user
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Index untuk query cepat
            $table->index(['line_group', 'sub_line']);
            $table->index('test_date');
            $table->index('product_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipc_product_checks');
    }
};
