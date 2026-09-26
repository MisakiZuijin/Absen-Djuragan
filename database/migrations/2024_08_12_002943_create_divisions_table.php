<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string("name", 30);
            $table->text("meet_url")->nullable();
            $table->text("icon")->nullable();
            $table->text("description")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('divisions');
    }
};
