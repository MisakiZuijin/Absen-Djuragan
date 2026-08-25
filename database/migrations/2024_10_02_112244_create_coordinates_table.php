<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('coordinates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("office_id")->nullable(false);
            $table->double("latitude")->nullable(false);
            $table->double("longitude")->nullable(false);
            $table->boolean("is_main")->nullable(false);

            $table->foreign("office_id")->references("id")->on("offices");
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('coordinates');
    }
};
