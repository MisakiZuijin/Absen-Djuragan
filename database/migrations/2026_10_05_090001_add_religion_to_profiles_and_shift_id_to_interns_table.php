<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom religion ke tabel profiles
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'religion')) {
                $table->string('religion', 30)->nullable()->after('gender');
            }
        });

        // 2. Tambah kolom shift_id ke tabel interns
        Schema::table('interns', function (Blueprint $table) {
            if (!Schema::hasColumn('interns', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('division_id');
                $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interns', function (Blueprint $table) {
            if (Schema::hasColumn('interns', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }
        });

        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'religion')) {
                $table->dropColumn('religion');
            }
        });
    }
};
