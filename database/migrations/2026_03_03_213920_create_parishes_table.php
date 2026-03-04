<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParishesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parishes', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->integer('deanery_id')->nullable();   // từ deanerys
            $table->integer('diocese_id')->nullable();   // từ diocese

            $table->string('ward')->nullable();
            $table->string('province')->nullable();
            $table->string('phone')->nullable(); // đổi int → string cho an toàn
            $table->text('image')->nullable();

            $table->boolean('status')->default(1);

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
        Schema::dropIfExists('parishes');
    }
}
