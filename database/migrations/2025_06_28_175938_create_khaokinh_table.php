<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKhaokinhTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('khaokinh', function (Blueprint $table) {
            $table->id();
            $table->integer('idh');
            $table->integer('lophoc');
            $table->integer('hocky');
            $table->integer('ngay');
            $table->integer('khaokinh');
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
        Schema::dropIfExists('khaokinh');
    }
}
