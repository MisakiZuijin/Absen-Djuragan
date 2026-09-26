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
            $table->unsignedBigInteger("permit_reason_id")->nullable(true);
            $table->boolean("isChangeSchedule")->default(false);
            $table->enum("work_type", ["wfo", "wfh"])->default("wfo");
            $table->boolean("isBackFirst")->default(false);
            $table->boolean("is_change_schedule_approved")->default(false);

            $table->foreign("permit_reason_id")->references("id")->on("permit_reasons");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table("detail_schedules", function (Blueprint $table) {
            $table->dropForeign(['permit_reason_id']);

            $table->dropColumn('permit_reason_id');
            $table->dropColumn('isChangeSchedule');
            $table->dropColumn('work_type');
        });
    }
};
