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

            // --- FIELD HASIL RINGKAS (SAMA DENGAN FORM) ---
            $table->decimal('avg_moisture_percent', 5, 2)->nullable(); // Kadar air %
            $table->decimal('avg_weight_g', 8, 3)->nullable();         // Berat g (rata-rata)

            // --- FIELD KHUSUS HITUNG KADAR AIR (LINE_TEH & LINE_POWDER) ---
            // Semua dibuat nullable, supaya fleksibel dan tidak wajib diisi jika tidak dipakai
            $table->decimal('cup_weight', 8, 3)->nullable();             // berat cawan porselin
            $table->decimal('product_weight', 8, 3)->nullable();         // berat produk
            $table->decimal('total_cup_plus_product', 8, 3)->nullable(); // total (cawan + produk)
            $table->decimal('weighing_1', 8, 3)->nullable();             // penimbangan 1
            $table->decimal('weighing_2', 8, 3)->nullable();             // penimbangan 2

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
