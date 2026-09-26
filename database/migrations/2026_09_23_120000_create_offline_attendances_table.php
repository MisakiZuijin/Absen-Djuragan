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
        Schema::create('offline_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->date('date')->index();
            $table->enum('status', ['hadir', 'terlambat', 'alpha'])->default('hadir');
            $table->time('check_time')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->string('penalty_type')->nullable(); // 'ganti_jam', 'tanpa_ganti_jam', 'dimaafkan'
            $table->integer('penalty_minutes')->default(0);
            $table->text('penalty_notes')->nullable();
            $table->string('penalty_by')->nullable();
            $table->timestamp('penalty_at')->nullable();
            $table->string('recorded_by')->nullable();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // 1 pemagang memiliki 1 status absen offline per hari
            $table->unique(['intern_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_attendances');
    }
};
