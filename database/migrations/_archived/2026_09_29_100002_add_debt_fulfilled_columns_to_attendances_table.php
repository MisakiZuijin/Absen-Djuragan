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
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'is_debt_fulfilled')) {
                $table->boolean('is_debt_fulfilled')->default(false)->after('auto_end_notified');
            }
            if (!Schema::hasColumn('attendances', 'debt_fulfilled_session_id')) {
                $table->foreignId('debt_fulfilled_session_id')->nullable()->after('is_debt_fulfilled')->constrained('change_time_sessions')->nullOnDelete();
            }
            if (!Schema::hasColumn('attendances', 'debt_fulfilled_at')) {
                $table->timestamp('debt_fulfilled_at')->nullable()->after('debt_fulfilled_session_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'debt_fulfilled_session_id')) {
                $table->dropForeign(['debt_fulfilled_session_id']);
                $table->dropColumn('debt_fulfilled_session_id');
            }
            if (Schema::hasColumn('attendances', 'is_debt_fulfilled')) {
                $table->dropColumn('is_debt_fulfilled');
            }
            if (Schema::hasColumn('attendances', 'debt_fulfilled_at')) {
                $table->dropColumn('debt_fulfilled_at');
            }
        });
    }
};
