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
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->enum('type', ['leave', 'toilet', 'prayer', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->string('authorized_by')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_in_minutes')->nullable();
            $table->unsignedInteger('agreed_duration_minutes')->nullable();
            $table->boolean('is_mandatory_replace')->default(false);
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_logs');
    }
};