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
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom department_id setelah email
            $table->unsignedBigInteger('department_id')->nullable()->after('is_active');

            // Foreign key
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('set null'); // jika department dihapus, user tetap ada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key dan kolom
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
