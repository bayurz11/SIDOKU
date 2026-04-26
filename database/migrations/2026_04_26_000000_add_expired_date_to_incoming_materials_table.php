<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incoming_materials', function (Blueprint $table) {
            if (! Schema::hasColumn('incoming_materials', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incoming_materials', function (Blueprint $table) {
            if (Schema::hasColumn('incoming_materials', 'expired_date')) {
                $table->dropColumn('expired_date');
            }
        });
    }
};
