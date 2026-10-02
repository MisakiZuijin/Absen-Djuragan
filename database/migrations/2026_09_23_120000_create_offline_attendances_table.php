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
        if (!Schema::hasTable('offline_attendances')) {
            Schema::create('offline_attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('intern_id')->constrained('interns')->onDelete('cascade');
                $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
                $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
                $table->date('date')->index();
                $table->string('status', 50)->default('hadir'); // hadir, early, terlambat, izin, sakit, alpha
                $table->string('approval_status', 20)->default('approved'); // approved, pending, rejected
                $table->string('sickness_verification_type', 50)->nullable();
                $table->boolean('permit_is_valid')->nullable();
                $table->time('check_time')->nullable();
                $table->time('physical_checkin_time')->nullable();
                $table->integer('late_minutes')->default(0);
                $table->text('notes')->nullable();
                $table->string('penalty_type')->nullable(); // 'ganti_jam', 'tanpa_ganti_jam', 'dimaafkan'
                $table->integer('penalty_minutes')->default(0);
                $table->text('penalty_notes')->nullable();
                $table->string('penalty_by')->nullable();
                $table->timestamp('penalty_at')->nullable();
                $table->string('recorded_by')->nullable();
                $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_note')->nullable();
                $table->timestamps();

                // 1 pemagang memiliki 1 status absen offline per hari
                $table->unique(['intern_id', 'date']);
            });
        } else {
            Schema::table('offline_attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('offline_attendances', 'physical_checkin_time')) {
                    $table->time('physical_checkin_time')->nullable()->after('check_time');
                }
                if (!Schema::hasColumn('offline_attendances', 'sickness_verification_type')) {
                    $table->string('sickness_verification_type', 50)->nullable()->after('status');
                }
                if (!Schema::hasColumn('offline_attendances', 'permit_is_valid')) {
                    $table->boolean('permit_is_valid')->nullable()->after('sickness_verification_type');
                }
                if (!Schema::hasColumn('offline_attendances', 'approval_status')) {
                    $table->string('approval_status', 20)->default('approved')->after('status');
                }
                if (!Schema::hasColumn('offline_attendances', 'approved_by')) {
                    $table->string('approved_by')->nullable()->after('recorded_by');
                }
                if (!Schema::hasColumn('offline_attendances', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('offline_attendances', 'rejection_note')) {
                    $table->text('rejection_note')->nullable()->after('approved_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_attendances');
    }
};
