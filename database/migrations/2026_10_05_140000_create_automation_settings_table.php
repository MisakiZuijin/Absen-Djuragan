<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('automation_settings')) {
            Schema::create('automation_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });

            // Seed default values
            $defaults = [
                [
                    'key' => 'rotation_anchor_date',
                    'value' => '2026-01-05',
                    'description' => 'Tanggal acuan awal rotasi mingguan (Senin awal tahun).',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'default_hybrid_pattern',
                    'value' => 'daily_alternating',
                    'description' => 'Pola hari kerja hybrid 50% (daily_alternating / block_3days).',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'overcrowded_threshold_percent',
                    'value' => '100',
                    'description' => 'Batas persentase kapasitas kursi sebelum beralih ke tier berikutnya.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'include_sundays',
                    'value' => '0',
                    'description' => 'Sertakan hari Minggu dalam penjadwalan otomatis (0 = tidak, 1 = ya).',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'skip_holidays',
                    'value' => '1',
                    'description' => 'Lewati hari libur nasional saat membuat jadwal (1 = ya, 0 = tidak).',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'enable_tier2_split_shift',
                    'value' => '1',
                    'description' => 'Aktifkan Tier 2 Shifting & Split-Shift WFH 3 jam saat kapasitas mulai penuh.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'enable_tier3_hybrid_rolling',
                    'value' => '1',
                    'description' => 'Aktifkan Tier 3 Hybrid 50% Rolling Mingguan saat kapasitas sangat padat.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('automation_settings')->insertOrIgnore($defaults);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_settings');
    }
};
