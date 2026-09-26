<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('log_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId("status_id")->default(1)->constrained("statuses");
            $table->date("date");
            $table->text("activity");
            $table->text("assistant_notes")->nullable();
            $table->boolean("is_approved")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('log_activities');
    }
};
