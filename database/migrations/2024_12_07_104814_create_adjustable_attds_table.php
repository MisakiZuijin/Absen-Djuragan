<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('adjustable_attds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->foreignId('detail_schedule_id')->constrained('detail_schedules');
            $table->date("date");
            $table->time("start_time")->nullable();
            $table->time("break_time")->nullable();
            $table->time("back_time")->nullable();
            $table->time("end_time")->nullable();
            $table->integer("total_min")->default(0);
            $table->integer("total_break_min")->default(0);
            $table->string("start_time_message")->nullable();
            $table->string("break_time_message")->nullable();
            $table->string("back_time_message")->nullable();
            $table->string("end_time_message")->nullable();
            $table->double("latitude_start")->nullable();
            $table->double("longitude_start")->nullable();
            $table->double("latitude_end")->nullable();
            $table->double("longitude_end")->nullable();
            $table->integer("is_approved")->default(0);
            $table->time("original_start_time");
            $table->time("original_end_time");
            $table->time("adjusted_start_time");
            $table->time("adjusted_end_time");

            $table->index(['intern_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('adjustable_attds');
    }
};
