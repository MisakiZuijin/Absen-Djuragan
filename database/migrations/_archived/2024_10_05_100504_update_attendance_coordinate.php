<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('attendances', function (Blueprint $table) {
            $table->double("latitude_start")->nullable(true);
            $table->double("longitude_start")->nullable(true);

            $table->double("latitude_end")->nullable(true);
            $table->double("longitude_end")->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn("latitude_start");
            $table->dropColumn("longitude_start");

            $table->dropColumn("latitude_end");
            $table->dropColumn("longitude_end");
        });
    }
};
