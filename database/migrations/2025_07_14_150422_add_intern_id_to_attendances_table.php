<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('intern_id')->after('id')->constrained('interns')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['intern_id']);
            $table->dropColumn('intern_id');
        });
    }
};
