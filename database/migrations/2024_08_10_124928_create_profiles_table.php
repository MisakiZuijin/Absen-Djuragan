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
            $table->foreignId("user_id")->constrained("users");
            $table->string("NIP")->nullable();
            $table->string("full_name", 100);
            $table->string("address", 100)->nullable();
            $table->string("phone", 16);
            $table->date("date_of_birth");
            $table->string("birth_place", 20);
            $table->string("gender", 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('profiles');
    }
};
