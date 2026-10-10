<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'is_rolling')) {
                $table->boolean('is_rolling')->default(false)->after('is_active');
            }
        });

        // Set default is_rolling = true for Pagi and Siang shifts
        DB::table('shifts')
            ->whereIn('name', ['Pagi', 'Siang'])
            ->orWhereIn('id', [2, 4])
            ->update(['is_rolling' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            if (Schema::hasColumn('shifts', 'is_rolling')) {
                $table->dropColumn('is_rolling');
            }
        });
    }
};
