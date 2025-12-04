<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumLopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lop', function (Blueprint $table) {
            $table->integer('did')->nullable()->after('id');
            $table->integer('deid')->nullable()->after('did');
            $table->integer('pid')->nullable()->after('deid');
            $table->integer('paid')->nullable()->after('pid');
            $table->date('start_date_one')->nullable()->after('paid');
            $table->date('end_date_one')->nullable()->after('start_date_one');
            $table->date('start_date_two')->nullable()->after('end_date_one');
            $table->date('end_date_two')->nullable()->after('start_date_two');
            $table->longtext('teacher')->nullable()->after('name');
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
