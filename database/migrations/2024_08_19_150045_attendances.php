<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->date("date");
            $table->time("start_time")->nullable();
            $table->time("break_time")->nullable();
            $table->time("back_time")->nullable();
            $table->time("permit_start")->nullable();
            $table->time("permit_back")->nullable();
            $table->integer("prayer_duration")->nullable();
            $table->time("end_time")->nullable();
            $table->time("adjusted_end_time")->nullable();
            $table->text("checkout_notes")->nullable();
            $table->integer("total_min")->default(0);
            $table->integer("total_break_min")->default(0);
            $table->integer("total_permit_min")->default(0);
            $table->string("keterangan")->nullable();
            $table->string("authorized_by")->nullable();
            $table->text("proof_link")->nullable();
            $table->enum("permit_type", ['toilet', 'leave', 'prayer', 'other'])->nullable();
            $table->string("permit_description")->nullable();
            $table->string("permit_authorized_by")->nullable();
            $table->boolean("is_auto_end")->default(false);
            $table->string("start_time_message")->nullable();
            $table->string("break_time_message")->nullable();
            $table->string("back_time_message")->nullable();
            $table->string("permit_start_message")->nullable();
            $table->string("permit_back_message")->nullable();
            $table->string("end_time_message")->nullable();
            $table->double("latitude_start")->nullable();
            $table->double("longitude_start")->nullable();
            $table->double("latitude_end")->nullable();
            $table->double("longitude_end")->nullable();

            $table->index('date');
            $table->index(['intern_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('attendances');
    }
};
