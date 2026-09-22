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
        Schema::create('checkin_messages', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['on_time', 'late'])->unique();
            $table->text('message'); // Teks popup
            $table->string('image')->nullable(); // Path gambar opsional
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed data default
        DB::table('checkin_messages')->insert([
            [
                'type' => 'on_time',
                'message' => 'Selamat datang! Anda tepat waktu hari ini 🎉',
                'image' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'late',
                'message' => 'Anda terlambat hari ini. Harap datang tepat waktu!',
                'image' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkin_messages');
    }
};
