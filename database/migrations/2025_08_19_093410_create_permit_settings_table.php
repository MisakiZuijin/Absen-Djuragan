<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permit_settings')) {
            Schema::create('permit_settings', function (Blueprint $table) {
                $table->id();
                $table->string('type')->unique(); // 'toilet', 'prayer', 'leave'
                $table->unsignedInteger('max_daily_count')->default(3); // Batas harian (0 = tanpa batas)
                $table->unsignedInteger('max_duration_minutes')->default(0); // Batas durasi per sesi menit (0 = tanpa batas)
                $table->timestamps();
            });

            // Menambahkan data default
            DB::table('permit_settings')->insert([
                ['type' => 'prayer', 'max_daily_count' => 5, 'max_duration_minutes' => 15, 'created_at' => now(), 'updated_at' => now()],
                ['type' => 'toilet', 'max_daily_count' => 0, 'max_duration_minutes' => 10, 'created_at' => now(), 'updated_at' => now()],
                ['type' => 'leave',  'max_daily_count' => 1, 'max_duration_minutes' => 0,  'created_at' => now(), 'updated_at' => now()],
            ]);
        } else {
            Schema::table('permit_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('permit_settings', 'max_duration_minutes')) {
                    $table->unsignedInteger('max_duration_minutes')->default(0)->after('max_daily_count');
                }
            });

            // Pastikan data default untuk prayer, toilet, leave ada
            $defaults = [
                ['type' => 'prayer', 'max_daily_count' => 5, 'max_duration_minutes' => 15],
                ['type' => 'toilet', 'max_daily_count' => 0, 'max_duration_minutes' => 10],
                ['type' => 'leave',  'max_daily_count' => 1, 'max_duration_minutes' => 0],
            ];

            foreach ($defaults as $def) {
                if (!DB::table('permit_settings')->where('type', $def['type'])->exists()) {
                    DB::table('permit_settings')->insert(array_merge($def, ['created_at' => now(), 'updated_at' => now()]));
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_settings');
    }
};
