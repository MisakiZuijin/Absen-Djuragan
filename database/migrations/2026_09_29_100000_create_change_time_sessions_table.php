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
        Schema::create('change_time_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->date('session_date');

            // Shift & Office yang digunakan saat sesi ganti jam
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();

            // Rekaman Waktu Absen Sesi Ganti Jam
            $table->time('start_time');
            $table->time('break_time')->nullable();
            $table->time('back_time')->nullable();
            $table->time('end_time')->nullable();

            // Total Durasi
            $table->integer('total_work_minutes')->default(0);
            $table->integer('total_break_minutes')->default(0);
            $table->integer('total_target_debt_minutes')->default(0);

            // Status Sesi & Approval Admin
            $table->enum('status', ['active', 'pending_approval', 'approved', 'rejected'])->default('active');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_note')->nullable();

            // Pesan / Catatan
            $table->text('start_time_message')->nullable();
            $table->text('break_time_message')->nullable();
            $table->text('back_time_message')->nullable();
            $table->text('end_time_message')->nullable();

            // Lokasi GPS
            $table->double('latitude_start')->nullable();
            $table->double('longitude_start')->nullable();
            $table->double('latitude_end')->nullable();
            $table->double('longitude_end')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['intern_id', 'session_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_time_sessions');
    }
};
