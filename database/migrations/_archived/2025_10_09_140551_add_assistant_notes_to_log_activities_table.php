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
        Schema::table('log_activities', function (Blueprint $table) {
            // Menambahkan kolom 'assistant_notes' setelah kolom 'activity'
            // Tipe TEXT digunakan agar bisa menampung catatan yang panjang
            // nullable() berarti kolom ini boleh kosong (karena opsional)
            $table->text('assistant_notes')->nullable()->after('activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_activities', function (Blueprint $table) {
            // Perintah untuk menghapus kolom jika migrasi di-rollback
            $table->dropColumn('assistant_notes');
        });
    }
};