<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlugsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::create('slugs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });*/
        Schema::create('slugs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('keyword')->unique();
            $table->string('controller');
            $table->string('model');
            $table->string('method')->default('show');
            $table->unsignedBigInteger('sluggable_id')->nullable();
            $table->timestamps();
            //$table->string('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slugs');
    }
}
