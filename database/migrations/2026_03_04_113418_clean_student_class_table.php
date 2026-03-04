<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanStudentClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Xóa record có student_id không tồn tại
        DB::statement("
            DELETE sc
            FROM students_class sc
            LEFT JOIN students s ON sc.student_id = s.id
            WHERE s.id IS NULL
        ");

        // Xóa record có class_id không tồn tại
        DB::statement("
            DELETE sc
            FROM students_class sc
            LEFT JOIN classes c ON sc.class_id = c.id
            WHERE c.id IS NULL
        "); 
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
