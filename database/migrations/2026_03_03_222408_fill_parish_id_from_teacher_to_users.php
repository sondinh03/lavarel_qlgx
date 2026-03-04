<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FillParishIdFromTeacherToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            UPDATE users u
            JOIN teacher t ON t.user_id = u.id
            SET u.parish_id = t.pid
            WHERE u.parish_id IS NULL
              AND t.pid IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            UPDATE users u
            JOIN teacher t ON t.user_id = u.id
            SET u.parish_id = NULL
            WHERE t.pid IS NOT NULL
        ");
    }
}
