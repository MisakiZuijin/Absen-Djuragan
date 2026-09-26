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
        Schema::table('attendances', function (Blueprint $table) {
            // Menambahkan kolom untuk menyimpan alasan izin keluar
            $table->string('permit_description')->nullable()->after('permit_type');
            
            // Menambahkan kolom untuk menyimpan nama yang mengizinkan
            $table->string('permit_authorized_by')->nullable()->after('permit_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Perintah untuk menghapus kolom jika migrasi di-rollback
            $table->dropColumn('permit_description');
            $table->dropColumn('permit_authorized_by');
        });
    }
};