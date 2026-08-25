<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing enum column
        DB::statement("ALTER TABLE attendances MODIFY COLUMN permit_type VARCHAR(255)");

        // Recreate the enum column with the new values
        DB::statement("ALTER TABLE attendances MODIFY COLUMN permit_type ENUM('toilet', 'leave', 'prayer', 'other') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE attendances MODIFY COLUMN permit_type VARCHAR(255)");
        DB::statement("ALTER TABLE attendances MODIFY COLUMN permit_type ENUM('toilet', 'leave', 'other') NULL");
    }
};
