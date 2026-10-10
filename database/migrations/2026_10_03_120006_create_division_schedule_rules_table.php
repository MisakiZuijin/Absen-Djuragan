<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('division_schedule_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->unique()->constrained('divisions')->cascadeOnDelete();
            $table->json('allowed_shift_ids')->nullable();
            $table->string('shift_distribution_mode', 50)->default('balanced_quota');
            $table->boolean('allow_hybrid_balancing')->default(true);
            $table->boolean('allow_split_shift')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('division_schedule_rules');
    }
};
