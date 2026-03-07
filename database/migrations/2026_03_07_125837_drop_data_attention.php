<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropDataAttention extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Xóa hết dữ liệu (records trước, sessions sau vì FK)
        DB::table('attendance_records')->truncate();
        DB::table('attendance_sessions')->truncate();

        // 2. Đổi kiểu created_by, updated_by từ varchar → bigint unsigned
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('updated_by')->nullable()->change();
        });

        // 3. Thêm FK
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreign('session_id')
                ->references('id')->on('attendance_sessions')
                ->onDelete('cascade');

            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // Tránh điểm danh trùng
            $table->unique(['session_id', 'student_id']);
        });

        // 4. FK cho attendance_sessions
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->foreign('class_id')
                ->references('id')->on('lop')
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
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropUnique(['session_id', 'student_id']);

            $table->string('created_by')->nullable()->change();
            $table->string('updated_by')->nullable()->change();
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });
    }
}
