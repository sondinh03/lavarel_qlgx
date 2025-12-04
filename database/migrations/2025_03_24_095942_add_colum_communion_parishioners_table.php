<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumCommunionParishionersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parishioners', function (Blueprint $table) {
            $table->integer('communion_dioceses')->nullable()->after('communion_giver');
            $table->integer('communion_deanerys')->nullable()->after('communion_dioceses');
            $table->integer('communion_parish')->nullable()->after('communion_deanerys');
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
