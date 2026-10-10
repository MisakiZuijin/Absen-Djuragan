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
        Schema::create('intern_conflict_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id_1')->constrained('interns')->cascadeOnDelete();
            $table->foreignId('intern_id_2')->constrained('interns')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['intern_id_1', 'intern_id_2']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_conflict_rules');
    }
};
