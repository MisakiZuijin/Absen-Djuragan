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
        if (!Schema::hasTable('change_time_notes')) {
            Schema::create('change_time_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->nullable()->constrained('change_time_sessions')->cascadeOnDelete();
                $table->foreignId('registration_id')->nullable()->constrained('change_time_registrations')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('message');
                $table->boolean('is_from_admin')->default(false);
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->index(['session_id', 'created_at']);
                $table->index(['session_id', 'is_from_admin', 'is_read']);
                $table->index(['registration_id', 'created_at']);
                $table->index(['registration_id', 'is_from_admin', 'is_read']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_time_notes');
    }
};
