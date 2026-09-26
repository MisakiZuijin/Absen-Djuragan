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
        Schema::table('offices', function (Blueprint $table) {
            $table->text('sop_url')->nullable()->after('capacity');
            $table->text('rules_url')->nullable()->after('sop_url');
            $table->text('rules_description')->nullable()->after('rules_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn(['sop_url', 'rules_url', 'rules_description']);
        });
    }
};
