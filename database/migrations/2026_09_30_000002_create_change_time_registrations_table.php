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
        if (!Schema::hasTable('change_time_registrations')) {
            Schema::create('change_time_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
                $table->date('requested_date')->nullable(); // Tanggal rencana ganti jam (minimal H+1 saat didaftarkan / maksimal pendaftaran H-1, atau dijadwalkan admin)
                $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
                $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
                $table->json('target_schedule_ids')->nullable();
                $table->integer('estimated_minutes')->default(0);
                $table->text('reason')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                // Indexes
                $table->index(['intern_id', 'requested_date']);
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_time_registrations');
    }
};
