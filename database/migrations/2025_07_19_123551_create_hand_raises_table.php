<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hand_raises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->string('type', 30)->default('question')->index();
            $table->string('presentation_mode', 20)->nullable();
            $table->text('meet_url')->nullable();
            $table->date('presentation_date')->nullable()->index();
            $table->time('scheduled_time')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->text('reason')->nullable();
            $table->decimal('performance_rating', 5, 2)->nullable();
            $table->text('performance_notes')->nullable();
            $table->text('admin_response')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('notification_seen_at')->nullable();
            $table->boolean('is_raised')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('hand_raises');
    }
};
