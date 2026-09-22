<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intern_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->unique()->constrained('interns')->cascadeOnDelete();
            $table->string('gdrive_url', 500)->nullable();
            $table->string('github_url', 255)->nullable();
            $table->string('gmail_account', 255)->nullable();
            $table->text('gmail_password')->nullable();
            $table->string('figma_url', 500)->nullable();
            $table->json('social_media_links')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_accounts');
    }
};
