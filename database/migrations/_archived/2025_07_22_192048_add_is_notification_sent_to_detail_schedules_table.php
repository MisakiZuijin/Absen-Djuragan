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
            $table->boolean('is_notification_sent')->default(false)->after('is_break_first');
        });
    }
    public function down(): void
    {
        Schema::table('detail_schedules', function (Blueprint $table) {
            $table->dropColumn('is_notification_sent');
        });
    }
};
