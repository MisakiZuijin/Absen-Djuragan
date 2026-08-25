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
        Schema::table('hand_raises', function (Blueprint $table) {
            // Tambahkan kolom jika belum ada
            if (!Schema::hasColumn('hand_raises', 'reason')) {
                $table->text('reason')->nullable()->after('project_id');
            }
            
            if (!Schema::hasColumn('hand_raises', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('reason');
            }
            
            if (!Schema::hasColumn('hand_raises', 'resolved_by')) {
                $table->unsignedBigInteger('resolved_by')->nullable()->after('resolved_at');
                $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            }

            // Tambahkan index untuk performa
            if (!Schema::hasIndex('hand_raises', ['is_raised'])) {
                $table->index(['is_raised']);
            }
            
            if (!Schema::hasIndex('hand_raises', ['created_at'])) {
                $table->index(['created_at']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hand_raises', function (Blueprint $table) {
            $table->dropIndex(['is_raised']);
            $table->dropIndex(['created_at']);
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['reason', 'resolved_at', 'resolved_by']);
        });
    }
};