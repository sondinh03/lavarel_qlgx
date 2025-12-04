<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumMorePowerParishionersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parishioners', function (Blueprint $table) {
            $table->integer('more_power_dioceses')->nullable()->after('more_power_sponsor');
            $table->integer('more_power_deanerys')->nullable()->after('more_power_dioceses');
            $table->integer('more_power_parish')->nullable()->after('more_power_deanerys');
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
