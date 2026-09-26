<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('interns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("school_id")->nullable()->default(null);
            $table->unsignedBigInteger("division_id")->nullable()->default(null);
            $table->unsignedBigInteger("brand_id")->nullable()->default(null);
            $table->string("nim", 50)->nullable();
            $table->string("attention_message")->nullable();
            $table->date("start_date")->nullable();
            $table->date("end_date")->nullable();

            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("school_id")->references("id")->on("schools");
            $table->foreign("division_id")->references("id")->on("divisions");
            $table->foreign("brand_id")->references("id")->on("brands")->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('interns');
    }
};
