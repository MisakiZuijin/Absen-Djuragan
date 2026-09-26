<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('name_project_id')->constrained('name_projects');
            $table->string("team");
            $table->string("description");
            $table->string('repository_url', 500)->nullable();
            $table->enum('status', ['progress', 'done']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('projects');
    }
};
