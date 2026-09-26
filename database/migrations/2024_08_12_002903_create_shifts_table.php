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
            $table->string("type")->nullable();
            $table->string("name", 100);
            $table->string("description")->nullable();
            $table->time("start_time");
            $table->time("end_time");
            $table->time("start_break_time")->nullable();
            $table->time("end_break_time")->nullable();
            $table->string("day_additional")->nullable();
            $table->time("adt_start_break_time")->nullable();
            $table->time("adt_end_break_time")->nullable();
            $table->integer("break_time_in_minute")->default(0);
            $table->boolean("is_active")->default(true);
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
