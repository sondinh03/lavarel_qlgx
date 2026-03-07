<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanClassTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Xóa các bản ghi có class_id không tồn tại trong bảng lop
        $deletedByClass = DB::table('class_teachers')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('classes')
                    ->whereColumn('classes.id', 'class_teachers.class_id');
            })
            ->delete();

        // 2. Xóa các bản ghi có teacher_id không tồn tại trong bảng teachers
        $deletedByTeacher = DB::table('class_teachers')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('teachers')
                    ->whereColumn('teachers.id', 'class_teachers.teacher_id');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
