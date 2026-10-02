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
        Schema::table('change_time_registrations', function (Blueprint $table) {
            $table->date('requested_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('change_time_registrations', function (Blueprint $table) {
            $table->date('requested_date')->nullable(false)->change();
        });
    }
};
