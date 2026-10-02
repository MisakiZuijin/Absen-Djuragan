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
        Schema::create('change_time_session_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('change_time_session_id')->constrained('change_time_sessions')->cascadeOnDelete();
            $table->foreignId('detail_schedule_id')->constrained('detail_schedules')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();

            // Rincian Hutang
            $table->integer('debt_minutes')->default(0);
            $table->integer('paid_minutes')->default(0);
            $table->boolean('is_fulfilled')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['change_time_session_id', 'detail_schedule_id'], 'idx_session_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_time_session_targets');
    }
};
