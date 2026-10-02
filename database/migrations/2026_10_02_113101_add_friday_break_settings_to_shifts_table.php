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
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('is_friday_break_active')->default(false)->after('end_break_time');
            $table->time('friday_start_break_time')->nullable()->after('is_friday_break_active');
            $table->time('friday_end_break_time')->nullable()->after('friday_start_break_time');
            $table->integer('friday_break_time_in_minute')->default(0)->after('friday_end_break_time');
        });

        // Seed existing Middle shifts with default Friday male break settings (11:40 - 12:40, 60 mins)
        \Illuminate\Support\Facades\DB::table('shifts')
            ->where('name', 'like', '%middle%')
            ->update([
                'is_friday_break_active' => true,
                'friday_start_break_time' => '11:40:00',
                'friday_end_break_time' => '12:40:00',
                'friday_break_time_in_minute' => 60,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn([
                'is_friday_break_active',
                'friday_start_break_time',
                'friday_end_break_time',
                'friday_break_time_in_minute',
            ]);
        });
    }
};
