<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('is_split_shift')->default(false)->after('friday_break_time_in_minute');
            $table->time('split_wfh_start')->nullable()->after('is_split_shift');
            $table->time('split_wfh_end')->nullable()->after('split_wfh_start');
            $table->time('split_wfo_start')->nullable()->after('split_wfh_end');
            $table->time('split_wfo_end')->nullable()->after('split_wfo_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn([
                'is_split_shift',
                'split_wfh_start',
                'split_wfh_end',
                'split_wfo_start',
                'split_wfo_end',
            ]);
        });
    }
};
