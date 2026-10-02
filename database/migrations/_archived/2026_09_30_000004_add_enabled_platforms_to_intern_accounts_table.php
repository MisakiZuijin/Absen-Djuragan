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
        if (Schema::hasTable('intern_accounts')) {
            Schema::table('intern_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('intern_accounts', 'enabled_platforms')) {
                    $table->json('enabled_platforms')->nullable()->after('spreadsheet_url');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('intern_accounts')) {
            Schema::table('intern_accounts', function (Blueprint $table) {
                if (Schema::hasColumn('intern_accounts', 'enabled_platforms')) {
                    $table->dropColumn('enabled_platforms');
                }
            });
        }
    }
};
