<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::table('adjustable_attds', function (Blueprint $table) {
        // Tambahkan kolom intern_id sebagai nullable dulu
        $table->unsignedBigInteger('intern_id')->after('id')->nullable();

        // Tambahkan index untuk performance
        $table->index(['intern_id', 'date']);
    });

    // Update data yang ada dengan intern_id yang valid
    // Misalnya, set default value atau update berdasarkan relasi yang ada
    DB::table('adjustable_attds')->update(['intern_id' => 1]); // Ganti dengan logika yang sesuai

    // Set kolom menjadi not nullable setelah data diupdate
    Schema::table('adjustable_attds', function (Blueprint $table) {
        $table->unsignedBigInteger('intern_id')->nullable(false)->change();
    });

    // Tambahkan foreign key constraint setelah data konsisten
    Schema::table('adjustable_attds', function (Blueprint $table) {
        $table->foreign('intern_id')->references('id')->on('interns')->onDelete('cascade');
    });
}
};
