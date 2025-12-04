<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumFamilyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('family', function (Blueprint $table) {
            $table->integer('did')->after('id');
            $table->integer('deid')->after('did');
            $table->integer('pid')->after('deid');
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
