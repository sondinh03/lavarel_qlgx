<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateParishsToParishGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO parish_groups (
                id,
                parish_id,
                name,
                status,
                created_at,
                updated_at
            )
            SELECT
                id,
                pid,
                name,
                status,
                created_at,
                updated_at
            FROM parishs
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('parish_groups')->truncate();
    }
}
