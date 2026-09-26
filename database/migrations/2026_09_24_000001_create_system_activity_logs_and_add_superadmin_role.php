<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // 1. Create system_activity_logs table
        if (!Schema::hasTable('system_activity_logs')) {
            Schema::create('system_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_name', 255)->nullable();
                $table->string('user_role', 100)->nullable();
                $table->string('action', 50)->index(); // LOGIN, LOGOUT, CREATE, UPDATE, DELETE, APPROVE, REJECT, PENALTY, EXPORT
                $table->string('module', 100)->index(); // Auth, Presensi, Izin, Mentoring, Master, User Management, Shift, Setting
                $table->text('description');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('properties')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }

        // 2. Add Super Admin role (id: 7) to roles table
        if (Schema::hasTable('roles')) {
            DB::table('roles')->updateOrInsert(
                ['id' => 7],
                [
                    'name' => 'Super Admin',
                    'description' => 'Memiliki hak akses tertinggi terhadap seluruh sistem, manajemen akun admin, dan audit log aktivitas.'
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('system_activity_logs');
        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('id', 7)->delete();
        }
    }
};
