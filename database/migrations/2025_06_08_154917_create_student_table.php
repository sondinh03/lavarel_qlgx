<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student', function (Blueprint $table) {
            $table->id();
            $table->integer('did');
            $table->integer('deid');
            $table->integer('pid');
            $table->integer('paid');
            $table->string('mahv', 250)->nullable();
            $table->integer('magd')->nullable();
            $table->integer('magdcg')->nullable();
            $table->integer('holy')->nullable();
            $table->string('name');
            $table->date('birthday')->nullable();
            $table->integer('phone')->nullable();
            $table->string('origin')->nullable();
            $table->integer('ward')->nullable();
            $table->integer('district')->nullable();
            $table->string('province')->nullable();
            $table->string('father')->nullable();
            $table->string('mother')->nullable();
            $table->integer('cccd')->nullable();
            $table->string('email')->nullable();
            $table->date('baptism_date')->nullable();
            $table->integer('baptism_number')->nullable();
            $table->integer('baptism_giver')->nullable();
            $table->integer('baptism_sponsor')->nullable();
            $table->integer('baptism_dioceses')->nullable();
            $table->integer('baptism_deanerys')->nullable();
            $table->integer('baptism_parish')->nullable();
            $table->date('more_power_date')->nullable();
            $table->integer('more_power_number')->nullable();
            $table->integer('more_power_giver')->nullable();
            $table->integer('more_power_sponsor'->nullable());
            $table->integer('more_power_address')->nullable();
            $table->integer('more_power_dioceses')->nullable();
            $table->integer('more_power_deanerys')->nullable();
            $table->integer('more_power_parish')->nullable();
            $table->date('promise_day')->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('student');
    }
}
