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
            // Menambahkan kolom baru setelah kolom 'keterangan'
            $table->string('authorized_by')->nullable()->after('keterangan'); // Untuk nama HR
            $table->text('proof_link')->nullable()->after('authorized_by');    // Untuk link bukti
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['authorized_by', 'proof_link']);
        });
    }
};