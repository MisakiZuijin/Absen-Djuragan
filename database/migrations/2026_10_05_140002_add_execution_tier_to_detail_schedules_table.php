<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('detail_schedules') && !Schema::hasColumn('detail_schedules', 'execution_tier')) {
            Schema::table('detail_schedules', function (Blueprint $table) {
                $table->unsignedTinyInteger('execution_tier')->default(1)->after('hybrid_pattern');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('detail_schedules') && Schema::hasColumn('detail_schedules', 'execution_tier')) {
            Schema::table('detail_schedules', function (Blueprint $table) {
                $table->dropColumn('execution_tier');
            });
        }
    }
};
