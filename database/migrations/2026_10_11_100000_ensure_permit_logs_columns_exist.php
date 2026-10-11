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
        // 1. Pastikan kolom-kolom izin keluar di tabel permit_logs tersedia
        if (Schema::hasTable('permit_logs')) {
            Schema::table('permit_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('permit_logs', 'agreed_duration_minutes')) {
                    $table->unsignedInteger('agreed_duration_minutes')->nullable()->after('duration_in_minutes');
                }
                if (!Schema::hasColumn('permit_logs', 'is_mandatory_replace')) {
                    $table->boolean('is_mandatory_replace')->default(false)->after('agreed_duration_minutes');
                }
                if (!Schema::hasColumn('permit_logs', 'approval_status')) {
                    $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved')->nullable()->after('is_mandatory_replace');
                }
            });
        }

        // 2. Pastikan kolom ganti jam di attendances tersedia jika belum termigrasi
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'is_debt_fulfilled')) {
                    $table->boolean('is_debt_fulfilled')->default(false);
                }
                if (!Schema::hasColumn('attendances', 'debt_fulfilled_session_id')) {
                    $table->unsignedBigInteger('debt_fulfilled_session_id')->nullable();
                }
                if (!Schema::hasColumn('attendances', 'debt_fulfilled_at')) {
                    $table->timestamp('debt_fulfilled_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permit_logs')) {
            Schema::table('permit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('permit_logs', 'agreed_duration_minutes')) {
                    $table->dropColumn('agreed_duration_minutes');
                }
                if (Schema::hasColumn('permit_logs', 'is_mandatory_replace')) {
                    $table->dropColumn('is_mandatory_replace');
                }
                if (Schema::hasColumn('permit_logs', 'approval_status')) {
                    $table->dropColumn('approval_status');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'is_debt_fulfilled')) {
                    $table->dropColumn('is_debt_fulfilled');
                }
                if (Schema::hasColumn('attendances', 'debt_fulfilled_session_id')) {
                    $table->dropColumn('debt_fulfilled_session_id');
                }
                if (Schema::hasColumn('attendances', 'debt_fulfilled_at')) {
                    $table->dropColumn('debt_fulfilled_at');
                }
            });
        }
    }
};
