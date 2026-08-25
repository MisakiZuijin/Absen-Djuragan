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
        // Kita memberitahu Laravel untuk mengubah tabel 'users'
        Schema::table('users', function (Blueprint $table) {
            // Ubah kolom-kolom ini menjadi BOLEH KOSONG (nullable)
            // Metode ->change() diperlukan untuk menerapkan modifikasi
            $table->string('os')->nullable()->change();
            $table->string('browser')->nullable()->change();
            $table->string('device')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     * (Ini untuk membatalkan jika diperlukan)
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan kolom menjadi WAJIB DIISI (tidak nullable)
            $table->string('os')->nullable(false)->change();
            $table->string('browser')->nullable(false)->change();
            $table->string('device')->nullable(false)->change();
        });
    }
};