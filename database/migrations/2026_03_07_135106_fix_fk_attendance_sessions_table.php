<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixFkAttendanceSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            // Drop FK cũ trỏ vào lop
            $table->dropForeign(['class_id']);

            // Thêm FK mới trỏ vào classes
            $table->foreign('class_id')
                ->references('id')
                ->on('classes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropForeign(['class_id']);

            $table->foreign('class_id')
                ->references('id')
                ->on('lop')
                ->onDelete('cascade');
        });
    }
}
