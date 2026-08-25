<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->nullable(false);
            $table->string("NIP")->nullable(true);
            $table->string("full_name",  100)->nullable(false);
            $table->string("address", 100)->nullable(true);
            $table->string("phone", 16)->nullable(false);
            $table->date("date_of_birth")->nullable(false);
            $table->string("birth_place", 20)->nullable(false);

            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('profiles');
    }
};
