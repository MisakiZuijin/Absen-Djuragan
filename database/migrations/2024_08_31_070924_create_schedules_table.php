<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Livewire\on;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("intern_id");
            $table->unsignedBigInteger("office_id");
            $table->date("start_period")->nullable(false);
            $table->date("end_period")->nullable(false);
            $table->enum("type", ["weekly", "daily"]);

            $table->foreign("intern_id")->references("id")->on("interns");
            $table->foreign("office_id")->references("id")->on("offices");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('schedules');
    }
};
