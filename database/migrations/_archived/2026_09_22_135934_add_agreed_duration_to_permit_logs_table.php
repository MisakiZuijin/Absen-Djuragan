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
            // Waktu izin yang disepakati admin (dalam menit) — acuan hutang waktu (Fitur #4)
            $table->unsignedInteger('agreed_duration_minutes')->nullable()->after('duration_in_minutes');
            // Apakah pemagang wajib ganti jam atas izin keluar ini?
            $table->boolean('is_mandatory_replace')->default(false)->after('agreed_duration_minutes');
            // Status persetujuan admin
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_mandatory_replace');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permit_logs', function (Blueprint $table) {
            $table->dropColumn([
                'agreed_duration_minutes',
                'is_mandatory_replace',
                'approval_status',
            ]);
        });
    }
};
