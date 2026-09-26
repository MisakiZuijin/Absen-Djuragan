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
        Schema::table('adjustable_attds', function (Blueprint $table) {
            // [MODIFIKASI] Menambahkan pengecekan sebelum menambah kolom
            if (!Schema::hasColumn('adjustable_attds', 'start_time')) {
                $table->time('start_time')->nullable();
            }

            // [MODIFIKASI] Menambahkan pengecekan sebelum menambah kolom
            if (!Schema::hasColumn('adjustable_attds', 'end_time')) {
                $table->time('end_time')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adjustable_attds', function (Blueprint $table) {
            // [MODIFIKASI] Menambahkan pengecekan sebelum menghapus kolom
            // agar proses rollback juga aman
            if (Schema::hasColumn('adjustable_attds', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('adjustable_attds', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });
    }
};