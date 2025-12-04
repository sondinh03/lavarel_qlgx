<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumParishionersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parishioners', function (Blueprint $table) {
            $table->integer('pid')->nullable()->after('name');
            $table->integer('deid')->after('pid');
            $table->integer('did')->after('deid');
            $table->integer('paid')->after('did');
            $table->integer('ward')->after('paid');
            $table->integer('district')->after('ward');
            $table->string('province')->after('district');
            $table->integer('phone')->nullable()->after('province');
            $table->string('image')->nullable()->after('phone');
            $table->string('email')->nullable()->after('image');
            $table->string('father')->nullable()->after('email');
            $table->string('mother')->nullable()->after('father');
            $table->integer('sex')->after('mother');
            $table->date('birthday')->nullable()->after('sex');
            $table->integer('cccd')->nullable()->after('birthday');
            $table->integer('holy')->nullable()->after('cccd');
            $table->integer('ethnic')->nullable()->after('holy');
            $table->integer('career')->nullable()->after('ethnic');
            $table->integer('level')->nullable()->after('career');
            $table->integer('position')->nullable()->after('level');
            $table->integer('language')->nullable()->after('position');
            $table->integer('status')->after('language');
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
