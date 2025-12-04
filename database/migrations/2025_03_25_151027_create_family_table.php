<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFamilyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('family', function (Blueprint $table) {
            $table->id();
            $table->integer('mother');
            $table->integer('female');
            $table->string('household');
            $table->string('name');
            $table->string('dien');
            $table->integer('songuoi');
            $table->string('phone');
            $table->string('origin');
            $table->string('ward');
            $table->string('district');
            $table->string('province');
            $table->integer('noio');
            $table->integer('thongke');
            $table->text('note')->nullable();
            $table->string('image');
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
        Schema::dropIfExists('family');
    }
}
