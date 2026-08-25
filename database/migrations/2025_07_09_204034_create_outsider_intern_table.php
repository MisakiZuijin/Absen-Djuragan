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
       Schema::create('outsider_intern', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('outsider_id');
        $table->unsignedBigInteger('intern_id');
        $table->timestamps();

        $table->foreign('outsider_id')->references('id')->on('outsiders')->onDelete('cascade');
        $table->foreign('intern_id')->references('id')->on('interns')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outsider_intern');
    }
};
