<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropParishIdFromGradeLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->dropColumn('parish_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->unsignedBigInteger('parish_id')->nullable()->after('id');
        });
    }
}
