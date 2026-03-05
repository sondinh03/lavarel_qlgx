<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateGradeLevelIdInClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            UPDATE classes c
            JOIN grade_levels g
            ON c.name LIKE CONCAT(g.name, '%')
            SET c.grade_level_id = g.id
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
}
