<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string("name", 100)->nullable(false);
            $table->string("description")->nullable(true);
            $table->time("start_time")->nullable(false);
            $table->time("end_time")->nullable(false);
            $table->time("start_break_time")->nullable(true);
            $table->time("end_break_time")->nullable(true);
            $table->string("day_additional")->nullable(true);
            $table->time("adt_start_break_time")->nullable(true);
            $table->time("adt_end_break_time")->nullable(true);
            $table->integer("break_time_in_minute")->default(0);
            $table->integer("total_time_in_minute")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('shifts');
    }
};
