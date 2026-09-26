<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('late_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->foreignId('adjustable_attd_id')->nullable()->constrained('adjustable_attds')->nullOnDelete();
            $table->date('date');
            $table->timestamp('absen_time');
            $table->timestamp('original_absen_time')->nullable();
            $table->string('original_end_time', 10)->nullable();
            $table->time('scheduled_time');
            $table->integer('late_minutes');
            $table->enum('status', ['telat', 'tepat_waktu', 'lewat'])->default('telat');
            $table->enum('type', ['checkin', 'checkout']);
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('late_absences');
    }
};
