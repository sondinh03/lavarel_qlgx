<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dile', function (Blueprint $table) {
            $table->id();
            $table->integer('idh');
            $table->integer('lophoc');
            $table->integer('hocky');
            $table->integer('thang');
            $table->integer('ngay');
            $table->integer('dile');
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
        Schema::dropIfExists('dile');
    }
}
