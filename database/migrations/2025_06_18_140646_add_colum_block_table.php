<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumBlockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('block', function (Blueprint $table) {
            $table->integer('did')->nullable()->after('id');
            $table->integer('deid')->nullable()->after('did');
            $table->integer('pid')->nullable()->after('deid');
            $table->integer('paid')->nullable()->after('pid');
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
