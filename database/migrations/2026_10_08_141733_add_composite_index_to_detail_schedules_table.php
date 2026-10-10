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
        Schema::table('detail_schedules', function (Blueprint $table) {
            $table->index(['attd_status_id', 'date'], 'idx_ds_attd_status_date');
            $table->index(['office_id', 'date'], 'idx_ds_office_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_ds_attd_status_date');
            $table->dropIndex('idx_ds_office_date');
        });
    }
};
