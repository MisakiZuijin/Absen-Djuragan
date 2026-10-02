<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('broadcast_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->unique(['broadcast_id', 'shift_id']);
        });

        Schema::create('broadcast_office', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained()->cascadeOnDelete();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->unique(['broadcast_id', 'office_id']);
        });

        Schema::create('broadcast_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('report');
            $table->timestamps();
            $table->unique(['broadcast_id', 'user_id']);
        });

        if (!Schema::hasTable('broadcast_report_chats')) {
            Schema::create('broadcast_report_chats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('broadcast_report_id')->constrained('broadcast_reports')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('message');
                $table->boolean('is_from_admin')->default(false);
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->index(['broadcast_report_id', 'created_at'], 'idx_brc_report_created');
                $table->index(['broadcast_report_id', 'is_from_admin', 'is_read'], 'idx_brc_report_admin_read');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_report_chats');
        Schema::dropIfExists('broadcast_reports');
        Schema::dropIfExists('broadcast_office');
        Schema::dropIfExists('broadcast_shift');
    }
};
