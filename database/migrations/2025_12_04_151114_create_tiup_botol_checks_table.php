<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiup_botol_checks', function (Blueprint $table) {
            $table->id();

            // Informasi dasar
            $table->date('tanggal');
            $table->string('hari')->nullable(); // bisa diisi otomatis dari tanggal
            $table->string('nama_botol');       // contoh: Botol 350ml, 550ml, 1500ml

            // --- KONDISI BOTOL ---
            // Drop Test (Tidak Bocor / Bocor)
            $table->enum('drop_test', ['TDK_BCR', 'BCR'])
                ->nullable()
                ->comment('TDK_BCR = Tidak Bocor/Pecah, BCR = Bocor/Pecah');

            // Penyebaran Material Rata
            $table->enum('penyebaran_rata', ['OK', 'NOK'])
                ->nullable();

            // Bottom Botol Tidak Menonjol
            $table->enum('bottom_tidak_menonjol', ['OK', 'NOK'])
                ->nullable();

            // Tidak Ada Material Tersisa
            $table->enum('tidak_ada_material', ['OK', 'NOK'])
                ->nullable();

            // --- GAMBAR UNTUK SETIAP KONDISI ---
            $table->string('drop_test_image')->nullable();
            $table->string('penyebaran_rata_image')->nullable();
            $table->string('bottom_tidak_menonjol_image')->nullable();
            $table->string('tidak_ada_material_image')->nullable();

            // Catatan tambahan
            $table->text('catatan')->nullable();

            // User yang melakukan input
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiup_botol_checks');
    }
};
