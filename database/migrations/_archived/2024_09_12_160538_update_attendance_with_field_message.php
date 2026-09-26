<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {

        Schema::table("attendances", function (Blueprint $table) {
            $table->string("start_time_message")->nullable(true);
            $table->string("break_time_message")->nullable(true);
            $table->string("back_time_message")->nullable(true);
            $table->string("permit_start_message")->nullable(true);
            $table->string("permit_back_message")->nullable(true);
            $table->string("end_time_message")->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::table("attendances", function (Blueprint $table) {
            $table->dropColumn([
                "start_time_message",
                "break_time_message",
                "back_time_message",
                "permit_start_message",
                "permit_back_message",
                "end_time_message",
            ]);
        });
    }
};
