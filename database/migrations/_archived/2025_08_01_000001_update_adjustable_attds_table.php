<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('adjustable_attds', function (Blueprint $table) {
            $table->time('original_start_time');
            $table->time('original_end_time');
            $table->time('adjusted_start_time');
            $table->time('adjusted_end_time');
        });
    }

    public function down()
    {
        Schema::table('adjustable_attds', function (Blueprint $table) {
            // Revert back to original columns
            $table->dropColumn([
                'original_start_time',
                'original_end_time',
                'adjusted_start_time',
                'adjusted_end_time'
            ]);
        });
    }
};