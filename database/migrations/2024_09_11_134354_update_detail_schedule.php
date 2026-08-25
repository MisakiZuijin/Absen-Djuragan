<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table("detail_schedules", function (Blueprint $table) {
            $table->unsignedBigInteger("attd_status_id")->nullable(false)->default(1);

            $table->foreign("attd_status_id")->references("id")->on("attd_statuses");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table("detail_schedules", function (Blueprint $table) {
            $table->dropForeign(['attd_status_id']);
            $table->dropColumn('attd_status_id');
        });
    }
};
