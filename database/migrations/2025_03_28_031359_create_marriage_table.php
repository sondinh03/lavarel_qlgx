<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarriageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marriage', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->date('date')->nullable();
            $table->integer('sohonphoi')->nullable();
            $table->string('marriage_address')->nullable();
            $table->integer('marriage_ward');
            $table->integer('marriage_district');
            $table->string('marriage_province');
            $table->integer('priest')->nullable();
            $table->string('peopleone')->nullable();
            $table->string('peopletwo')->nullable();
            $table->integer('tinhtrang')->nullable();
            $table->text('marriage_note')->nullable();
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
        Schema::dropIfExists('marriage');
    }
}
