<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('change_time_settings')) {
            Schema::create('change_time_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('restrict_to_holidays')->default(false); // default false: jangan di-hide dulu
                $table->json('allowed_shift_ids')->nullable();
                $table->json('allowed_office_ids')->nullable();
                $table->unsignedBigInteger('default_office_id')->nullable()->default(1);
                $table->text('intern_notice_text')->nullable();
                $table->timestamps();
            });

            // Ambil ID shift selain 'none' (id > 1) dan kantor default (1)
            $shiftIds = DB::table('shifts')->where('id', '>', 1)->pluck('id')->toArray();
            $officeIds = DB::table('offices')->pluck('id')->toArray();
            if (empty($officeIds)) {
                $officeIds = [1];
            }

            DB::table('change_time_settings')->insert([
                'restrict_to_holidays' => false,
                'allowed_shift_ids' => json_encode($shiftIds),
                'allowed_office_ids' => json_encode($officeIds),
                'default_office_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('change_time_settings');
    }
};
