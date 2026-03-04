<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateLopToClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO classes (
                parish_id,
                school_year_id,
                grade_level_id,
                name,
                capacity,
                is_active,
                created_at,
                updated_at
            )
            SELECT
                lop.pid,
                lop.schoolyear,
                lop.block,
                lop.name,
                0,
                IF(lop.status = 1, 1, 0),
                lop.created_at,
                lop.updated_at
            FROM lop
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DELETE FROM classes");
    }
}
