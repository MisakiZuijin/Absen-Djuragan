<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('late_absences', function (Blueprint $table) {
            $table->timestamp('original_absen_time')->nullable()->after('absen_time');
            $table->string('original_end_time', 10)->nullable()->after('original_absen_time');
        });
    }

    public function down()
    {
        Schema::table('late_absences', function (Blueprint $table) {
            $table->dropColumn(['original_absen_time', 'original_end_time']);
        });
    }
};
