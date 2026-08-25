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
        Schema::table('permit_logs', function (Blueprint $table) {
            // Mengubah kolom agar mendukung presisi waktu (detik)
            $table->dateTime('start_time')->change();
            $table->dateTime('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permit_logs', function (Blueprint $table) {
            // (Opsional) Mengembalikan ke kondisi semula jika perlu rollback
            $table->dateTime('start_time')->change();
            $table->dateTime('end_time')->nullable()->change();
        });
    }
};