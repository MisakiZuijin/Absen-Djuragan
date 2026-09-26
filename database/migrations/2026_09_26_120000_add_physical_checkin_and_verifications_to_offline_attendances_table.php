<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offline_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('offline_attendances', 'physical_checkin_time')) {
                $table->time('physical_checkin_time')->nullable()->after('check_time');
            }
            if (!Schema::hasColumn('offline_attendances', 'sickness_verification_type')) {
                $table->string('sickness_verification_type', 50)->nullable()->after('status');
            }
            if (!Schema::hasColumn('offline_attendances', 'permit_is_valid')) {
                $table->boolean('permit_is_valid')->nullable()->after('sickness_verification_type');
            }
        });

        // Change status to string 50 to support 'hadir', 'early', 'terlambat', 'izin', 'sakit', 'alpha'
        try {
            DB::statement("ALTER TABLE offline_attendances MODIFY COLUMN status VARCHAR(50) DEFAULT 'hadir'");
        } catch (\Exception $e) {
            // Ignore if already varchar
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('offline_attendances', 'physical_checkin_time')) {
                $table->dropColumn('physical_checkin_time');
            }
            if (Schema::hasColumn('offline_attendances', 'sickness_verification_type')) {
                $table->dropColumn('sickness_verification_type');
            }
            if (Schema::hasColumn('offline_attendances', 'permit_is_valid')) {
                $table->dropColumn('permit_is_valid');
            }
        });
    }
};
