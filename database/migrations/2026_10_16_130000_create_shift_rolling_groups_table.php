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
        // 1. Buat tabel shift_rolling_groups
        Schema::create('shift_rolling_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('shift_id_1')->constrained('shifts')->cascadeOnDelete();
            $table->foreignId('shift_id_2')->constrained('shifts')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tambahkan shift_rolling_group_id ke tabel divisions
        Schema::table('divisions', function (Blueprint $table) {
            $table->foreignId('shift_rolling_group_id')
                ->nullable()
                ->after('meet_url')
                ->constrained('shift_rolling_groups')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropForeign(['shift_rolling_group_id']);
            $table->dropColumn('shift_rolling_group_id');
        });

        Schema::dropIfExists('shift_rolling_groups');
    }
};
