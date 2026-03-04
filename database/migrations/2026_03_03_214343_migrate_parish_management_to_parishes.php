<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateParishManagementToParishes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO parishes (
                id,
                name,
                deanery_id,
                diocese_id,
                ward,
                province,
                phone,
                image,
                status,
                created_at,
                updated_at
            )
            SELECT
                id,
                name,
                deanerys,
                diocese,
                ward,
                province,
                phone,
                image,
                status,
                created_at,
                updated_at
            FROM parish_managements
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('parishes')->truncate();
    }
}
