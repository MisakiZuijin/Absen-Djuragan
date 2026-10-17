<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            if (!Schema::hasColumn('divisions', 'max_logbook_per_day')) {
                $table->unsignedTinyInteger('max_logbook_per_day')->default(1)->after('description');
            }
            if (!Schema::hasColumn('divisions', 'logbook_start_time')) {
                $table->time('logbook_start_time')->nullable()->after('max_logbook_per_day');
            }
            if (!Schema::hasColumn('divisions', 'logbook_end_time')) {
                $table->time('logbook_end_time')->nullable()->after('logbook_start_time');
            }
        });

        Schema::table('log_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('log_activities', 'detail_schedule_id')) {
                $table->unsignedBigInteger('detail_schedule_id')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('log_activities', 'time')) {
                $table->time('time')->nullable()->after('date');
            }
        });

        // Backfill existing log_activities.detail_schedule_id dari detail_schedules.log_activity_id
        try {
            DB::statement("UPDATE log_activities la 
                INNER JOIN detail_schedules ds ON ds.log_activity_id = la.id 
                SET la.detail_schedule_id = ds.id 
                WHERE la.detail_schedule_id IS NULL");
        } catch (\Throwable $e) {
            // Silently handle if tables are empty or query fails in different drivers
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_activities', function (Blueprint $table) {
            if (Schema::hasColumn('log_activities', 'time')) {
                $table->dropColumn('time');
            }
            if (Schema::hasColumn('log_activities', 'detail_schedule_id')) {
                $table->dropColumn('detail_schedule_id');
            }
        });

        Schema::table('divisions', function (Blueprint $table) {
            if (Schema::hasColumn('divisions', 'logbook_end_time')) {
                $table->dropColumn('logbook_end_time');
            }
            if (Schema::hasColumn('divisions', 'logbook_start_time')) {
                $table->dropColumn('logbook_start_time');
            }
            if (Schema::hasColumn('divisions', 'max_logbook_per_day')) {
                $table->dropColumn('max_logbook_per_day');
            }
        });
    }
};
