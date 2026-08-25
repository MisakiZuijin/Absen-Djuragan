<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string("username", 30)->nullable(false)->unique("user_username_unique");
            $table->string("email", 100)->nullable(false)->unique("user_email_unique");
            $table->string("password", 60)->nullable(false);
            $table->unsignedBigInteger("role_id")->nullable(false);
            $table->string("os");
            $table->string('browser');
            $table->string("device");
            $table->boolean("is_reset_token")->default(false);
            $table->boolean("is_active")->default(false);
            $table->boolean("is_confirm")->default(false);
            $table->boolean("is_gps_support")->default(false);
            $table->boolean("is_gps_activate")->default(false);
            $table->timestamps();

            $table->foreign("role_id")->references("id")->on("roles");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('users');
    }
};
