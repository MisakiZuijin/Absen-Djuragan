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
        if (Schema::hasTable('offices') && !Schema::hasColumn('offices', 'piket_max_per_week')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->unsignedTinyInteger('piket_max_per_week')->default(1)->after('piket_description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('offices') && Schema::hasColumn('offices', 'piket_max_per_week')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->dropColumn('piket_max_per_week');
            });
        }
    }
};
