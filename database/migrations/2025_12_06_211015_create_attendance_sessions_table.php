<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('class_id');
            $table->date('date');

            $table->tinyInteger('type')->default(1); 
            // 1: hoc, 2: le, 3: khac...
            $table->tinyInteger('status')->default(1); 
            // 1: chờ xử lý, 2: đang hoạt động, 3: đã đóng, 4: vô hiệu hóa, 5: đã hủy

            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            // Index quan trọng
            $table->index('class_id');
            $table->index('date');
            $table->index(['class_id', 'date']);
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_sessions');
    }
}
