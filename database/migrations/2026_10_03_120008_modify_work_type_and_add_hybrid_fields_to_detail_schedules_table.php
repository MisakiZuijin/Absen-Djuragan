<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah enum work_type menjadi varchar(20) secara aman
        DB::statement("ALTER TABLE detail_schedules MODIFY work_type VARCHAR(20) NOT NULL DEFAULT 'wfo'");

        Schema::table('detail_schedules', function (Blueprint $table) {
            $table->string('hybrid_pattern', 20)->default('none')->after('work_type');
            $table->boolean('is_spillover')->default(false)->after('hybrid_pattern');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_schedules', function (Blueprint $table) {
            $table->dropColumn(['hybrid_pattern', 'is_spillover']);
        });

        DB::statement("ALTER TABLE detail_schedules MODIFY work_type ENUM('wfo', 'wfh') NOT NULL DEFAULT 'wfo'");
    }
};
