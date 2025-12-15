<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('student_id');

            $table->tinyInteger('status')->nullable();
            // 1: có mặt, 2: có phép, 3: vắng
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index('session_id');
            $table->index('student_id');
            $table->index(['student_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_records');
    }
}
