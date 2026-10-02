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
        if (!Schema::hasTable('popup_settings')) {
            Schema::create('popup_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->string('label', 255);
                $table->string('group', 100)->default('general');
                $table->boolean('is_enabled')->default(true);
                $table->integer('interval_seconds')->default(10);
                $table->integer('default_interval')->default(10);
                $table->text('description')->nullable();
                $table->timestamps();
            });

            // Seed default settings
            $defaults = [
                [
                    'key' => 'attd_status_button',
                    'label' => 'Status Absen Pemagang',
                    'group' => 'pemagang',
                    'is_enabled' => true,
                    'interval_seconds' => 5,
                    'default_interval' => 5,
                    'description' => 'Refresh status presensi real-time pemagang, timer jam kerja, dan popup tanggapan bantuan di dashboard.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'broadcast_popup',
                    'label' => 'Popup Pengumuman (Broadcast)',
                    'group' => 'pemagang',
                    'is_enabled' => true,
                    'interval_seconds' => 15,
                    'default_interval' => 15,
                    'description' => 'Pengecekan berkala kemunculan pesan siaran / pengumuman penting untuk pemagang.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'change_time_info',
                    'label' => 'Info Sesi Ganti Jam',
                    'group' => 'pemagang',
                    'is_enabled' => true,
                    'interval_seconds' => 10,
                    'default_interval' => 10,
                    'description' => 'Refresh berkala info status, durasi, dan timer sesi ganti jam yang sedang berlangsung.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'raise_hand_manager',
                    'label' => 'Tabel Antrean Raise Hand',
                    'group' => 'admin',
                    'is_enabled' => true,
                    'interval_seconds' => 5,
                    'default_interval' => 5,
                    'description' => 'Auto-refresh livewire untuk memuat data antrean bantuan & pertanyaan di halaman Admin Raise Hand.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'toilet_monitor',
                    'label' => 'Monitor Izin Toilet',
                    'group' => 'admin',
                    'is_enabled' => true,
                    'interval_seconds' => 15,
                    'default_interval' => 15,
                    'description' => 'Monitoring real-time intern yang sedang izin ke toilet pada halaman admin.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'raise_hand_notification',
                    'label' => 'Notifikasi Suara & Toast Global',
                    'group' => 'admin',
                    'is_enabled' => true,
                    'interval_seconds' => 5,
                    'default_interval' => 5,
                    'description' => 'Polling background JavaScript untuk pemutaran suara lonceng dan pop-up toast di seluruh halaman admin.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('popup_settings')->insert($defaults);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popup_settings');
    }
};
