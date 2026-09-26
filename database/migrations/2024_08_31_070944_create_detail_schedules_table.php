<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('detail_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId("schedule_id")->constrained("schedules");
            $table->foreignId("attendance_id")->nullable()->constrained("attendances");
            $table->foreignId("shift_id")->nullable()->constrained("shifts");
            $table->foreignId("office_id")->constrained("offices");
            $table->unsignedBigInteger("log_activity_id")->nullable();
            $table->date("date");
            $table->string("start_time", 12)->nullable();
            $table->string("end_time", 12)->nullable();
            $table->boolean("is_break_first")->default(false);
            $table->boolean("is_notification_sent")->default(false);
            $table->foreignId("attd_status_id")->default(1)->constrained("attd_statuses");
            $table->foreignId("permit_reason_id")->nullable()->constrained("permit_reasons");
            $table->boolean("isChangeSchedule")->default(false);
            $table->enum("work_type", ["wfo", "wfh"])->default("wfo");
            $table->boolean("isBackFirst")->default(false);
            $table->boolean("is_change_schedule_approved")->default(false);

            $table->index('date');
            $table->index('isChangeSchedule');
            $table->index(['schedule_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('detail_schedules');
    }
};
