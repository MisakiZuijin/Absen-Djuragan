<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->date("date")->nullable(false);
            $table->time("start_time")->nullable(true);
            $table->time("break_time")->nullable(true);
            $table->time("back_time")->nullable(true);
            $table->time("permit_start")->nullable(true);
            $table->time("permit_back")->nullable(true);
            $table->time("end_time")->nullable(true);
            $table->integer("total_min")->default(0);
            $table->integer("total_break_min")->default(0);
            $table->integer("total_permit_min")->default(0);
            $table->boolean("is_auto_end")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('attendancess');
    }
};
