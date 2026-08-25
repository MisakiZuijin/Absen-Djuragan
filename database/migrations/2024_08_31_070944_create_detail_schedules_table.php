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
            $table->unsignedBigInteger("schedule_id");
            $table->unsignedBigInteger("attendance_id")->nullable();
            $table->unsignedBigInteger("shift_id")->nullable(true);
            $table->unsignedBigInteger("office_id");
            $table->unsignedBigInteger("log_activity_id")->nullable(true);
            $table->date("date")->nullable(false);
            $table->string("start_time", 12)->nullable(true);
            $table->string("end_time", 12)->nullable(true);
            $table->boolean("is_break_first")->default(false);

            $table->foreign("shift_id")->references("id")->on("shifts");
            $table->foreign("attendance_id")->references("id")->on("attendances");
            $table->foreign("office_id")->references("id")->on("offices");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('detail_schedules');
    }
};
