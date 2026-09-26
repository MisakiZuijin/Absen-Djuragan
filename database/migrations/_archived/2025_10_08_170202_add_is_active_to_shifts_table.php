<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('shifts', function (Blueprint $table) {
        $table->boolean('is_active')->default(true)->after('break_time_in_minute');
    });
}

public function down()
{
    Schema::table('shifts', function (Blueprint $table) {
        $table->dropColumn('is_active');
    });
}
};
