<?php

use App\Utils\AdjustableStatus;
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
            $table->unsignedBigInteger("detail_schedule_id")->nullable(false);
            $table->date("date")->nullable(false);
            $table->time("start_time")->nullable(true);
            $table->time("break_time")->nullable(true);
            $table->time("back_time")->nullable(true);
            $table->time("end_time")->nullable(true);
            $table->integer("total_min")->default(0);
            $table->integer("total_break_min")->default(0);
            $table->string("start_time_message")->nullable(true);
            $table->string("break_time_message")->nullable(true);
            $table->string("back_time_message")->nullable(true);
            $table->string("end_time_message")->nullable(true);
            $table->double("latitude_start")->nullable(true);
            $table->double("longitude_start")->nullable(true);
            $table->double("latitude_end")->nullable(true);
            $table->double("longitude_end")->nullable(true);
            $table->integer("is_approved")->default(0);
            // $table->enum("is_approved", AdjustableStatus::toArray())->default(AdjustableStatus::PENDING);

            $table->foreign("detail_schedule_id")->references("id")->on("detail_schedules");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('adjustable_attds');
    }
};
