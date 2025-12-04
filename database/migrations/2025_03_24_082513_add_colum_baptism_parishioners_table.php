<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumBaptismParishionersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parishioners', function (Blueprint $table) {
            $table->integer('baptism_dioceses')->nullable()->after('baptism_sponsor');
            $table->integer('baptism_deanerys')->nullable()->after('baptism_dioceses');
            $table->integer('baptism_parish')->nullable()->after('baptism_deanerys');
        });
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
