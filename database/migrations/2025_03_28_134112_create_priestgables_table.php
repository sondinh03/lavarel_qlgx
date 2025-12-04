<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriestgablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('priestgables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('priest_id')->index('priestgable_priest_id_foreign');
            $table->unsignedBigInteger('priestgable_id');
            $table->string('priestgable_type');
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
        Schema::dropIfExists('priestgables');
    }
}
