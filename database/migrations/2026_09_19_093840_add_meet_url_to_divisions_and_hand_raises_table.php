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
        if (Schema::hasTable('divisions') && !Schema::hasColumn('divisions', 'meet_url')) {
            Schema::table('divisions', function (Blueprint $table) {
                $table->text('meet_url')->nullable()->after('name');
            });
        }

        if (Schema::hasTable('hand_raises') && !Schema::hasColumn('hand_raises', 'meet_url')) {
            Schema::table('hand_raises', function (Blueprint $table) {
                $table->text('meet_url')->nullable()->after('presentation_mode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('divisions') && Schema::hasColumn('divisions', 'meet_url')) {
            Schema::table('divisions', function (Blueprint $table) {
                $table->dropColumn('meet_url');
            });
        }

        if (Schema::hasTable('hand_raises') && Schema::hasColumn('hand_raises', 'meet_url')) {
            Schema::table('hand_raises', function (Blueprint $table) {
                $table->dropColumn('meet_url');
            });
        }
    }
};
