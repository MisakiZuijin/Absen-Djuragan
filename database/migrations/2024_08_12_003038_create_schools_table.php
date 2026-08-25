<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable(false);
            $table->string("address")->nullable(false);
            $table->unsignedBigInteger("educational_level_id");

            $table->foreign("educational_level_id")->references("id")->on("educational_levels");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('schools');
    }
};
