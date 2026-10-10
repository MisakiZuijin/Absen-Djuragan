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
        Schema::table('interns', function (Blueprint $table) {
            $table->decimal('home_latitude', 10, 8)->nullable()->after('end_date');
            $table->decimal('home_longitude', 11, 8)->nullable()->after('home_latitude');
            $table->string('home_address_notes')->nullable()->after('home_longitude');
            $table->string('work_placement_type', 30)->default('auto_office')->after('home_address_notes');
            $table->foreignId('locked_office_id')->nullable()->after('work_placement_type')->constrained('offices')->nullOnDelete();
            $table->foreignId('blacklisted_office_id')->nullable()->after('locked_office_id')->constrained('offices')->nullOnDelete();
            $table->text('hr_placement_notes')->nullable()->after('blacklisted_office_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            $table->dropForeign(['locked_office_id']);
            $table->dropForeign(['blacklisted_office_id']);
            $table->dropColumn([
                'home_latitude',
                'home_longitude',
                'home_address_notes',
                'work_placement_type',
                'locked_office_id',
                'blacklisted_office_id',
                'hr_placement_notes',
            ]);
        });
    }
};
