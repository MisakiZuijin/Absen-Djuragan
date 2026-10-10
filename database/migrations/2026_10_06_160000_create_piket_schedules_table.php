<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (!Schema::hasTable('piket_schedules')) {
            Schema::create('piket_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
                $table->foreignId('office_id')->constrained('offices')->cascadeOnDelete();
                $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
                $table->foreignId('detail_schedule_id')->nullable()->constrained('detail_schedules')->nullOnDelete();
                $table->date('date');
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->unique(['intern_id', 'date'], 'uniq_intern_piket_date');
                $table->index(['date', 'office_id', 'shift_id'], 'idx_piket_date_office_shift');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('piket_schedules');
    }
};
