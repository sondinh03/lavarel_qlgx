<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttributeParishIdIntoNamhocTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nam_hoc', function (Blueprint $table) {
            $table->integer('parish_id')->nullable()->after('name');
            $table->date('start_date_one')->nullable()->after('parish_id');
            $table->date('end_date_one')->nullable()->after('start_date_one');
            $table->date('start_date_two')->nullable()->after('end_date_one');
            $table->date('end_date_two')->nullable()->after('start_date_two');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nam_hoc', function (Blueprint $table) {
            //
        });
    }
}
