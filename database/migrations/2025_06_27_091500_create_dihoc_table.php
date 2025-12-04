<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDihocTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dihoc', function (Blueprint $table) {
            $table->id();
            $table->integer('idh');
            $table->integer('lophoc');
            $table->integer('hocky');
            $table->integer('tuan');
            $table->integer('dihoc');
            $table->integer('weight');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dihoc');
    }
}
