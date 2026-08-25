<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_logs', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel attendance utama
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            
            // Informasi izin
            $table->enum('type', ['leave', 'toilet', 'prayer', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->string('authorized_by')->nullable(); // Untuk 'izin keluar'
            
            // Waktu
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('duration_in_minutes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_logs');
    }
};