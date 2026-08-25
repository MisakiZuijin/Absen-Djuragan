<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLateAbsencesTable extends Migration
{
    public function up()
    {
        Schema::create('late_absences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intern_id');
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->unsignedBigInteger('adjustable_attd_id')->nullable(); // tambah relasi ke adjustable
            $table->date('date'); // tanggal absen
            $table->timestamp('absen_time'); // waktu absen sebenarnya
            $table->time('scheduled_time'); // waktu yang seharusnya
            $table->integer('late_minutes'); // durasi telat dalam menit
            $table->enum('status', ['telat', 'tepat_waktu', 'lewat'])->default('telat'); // status keterlambatan
            $table->enum('type', ['checkin', 'checkout']); // jenis absen

            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            // foreign key
            $table->foreign('intern_id')->references('id')->on('interns')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('set null');
            $table->foreign('adjustable_attd_id')->references('id')->on('adjustable_attds')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('late_absences');
    }
}
