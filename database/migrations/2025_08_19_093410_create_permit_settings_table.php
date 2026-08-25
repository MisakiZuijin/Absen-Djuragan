<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // 'toilet', 'prayer', 'leave'
            $table->unsignedInteger('max_daily_count')->default(3); // Batas harian
            $table->timestamps();
        });

        // Menambahkan data default
        DB::table('permit_settings')->insert([
            ['type' => 'prayer', 'max_daily_count' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'leave', 'max_daily_count' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_settings');
    }
};
