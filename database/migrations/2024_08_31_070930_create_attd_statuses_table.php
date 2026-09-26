<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('attd_statuses', function (Blueprint $table) {
            $table->id();
            $table->string("name", 30)->nullable(false);
            $table->string("description")->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('attd_statuses');
    }
};
