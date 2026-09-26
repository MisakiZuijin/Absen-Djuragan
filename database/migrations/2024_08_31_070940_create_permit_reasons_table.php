<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('permit_reasons', function (Blueprint $table) {
            $table->id();
            $table->string("description");
            $table->string("proof_url");
            $table->unsignedBigInteger("permit_category_id");

            $table->foreign("permit_category_id")->references("id")->on("permit_categories");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('permit_reasons');
    }
};
