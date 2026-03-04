<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateStudentToStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO students (
                student_code,
                parishioner_id,
                parish_id,
                saint_id,
                first_name,
                last_name,
                father_name,
                mother_name,
                birthday,
                phone,
                email,
                is_active,
                note,
                created_at,
                updated_at
            )
            SELECT
                mahv,
                parishioner_id,
                pid,
                holy,
                name,
                last_name,
                father,
                mother,
                birthday,
                phone_number,
                email,
                CASE WHEN status = 1 THEN 1 ELSE 0 END,
                note,
                NOW(),
                NOW()
            FROM student
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('students')->truncate();
    }
}
