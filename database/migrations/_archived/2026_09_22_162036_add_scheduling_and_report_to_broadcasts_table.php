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
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('broadcast_type');
            $table->boolean('requires_report')->default(false)->after('scheduled_at');
            $table->text('report_question')->nullable()->after('requires_report');
            $table->foreignId('created_by')->nullable()->after('report_question')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['scheduled_at', 'requires_report', 'report_question']);
        });
    }
};
