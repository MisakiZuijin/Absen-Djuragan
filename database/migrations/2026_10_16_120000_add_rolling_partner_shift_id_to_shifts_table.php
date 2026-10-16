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
        if (Schema::hasTable('shifts')) {
            Schema::table('shifts', function (Blueprint $table) {
                if (!Schema::hasColumn('shifts', 'rolling_partner_shift_id')) {
                    $table->unsignedBigInteger('rolling_partner_shift_id')->nullable()->after('is_rolling');
                    $table->foreign('rolling_partner_shift_id')
                        ->references('id')
                        ->on('shifts')
                        ->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shifts')) {
            Schema::table('shifts', function (Blueprint $table) {
                if (Schema::hasColumn('shifts', 'rolling_partner_shift_id')) {
                    $table->dropForeign(['rolling_partner_shift_id']);
                    $table->dropColumn('rolling_partner_shift_id');
                }
            });
        }
    }
};
