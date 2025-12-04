<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiaoXuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::create('giao_xu', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });*/
        
        Schema::create('parish_managements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('deanerys');
            $table->string('diocese');
            $table->string('ward');
            $table->string('district');
            $table->string('province');
            $table->integer('phone')->nullable();
            $table->text('image')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
        
        Schema::create('revisions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('revisionable_type');
            $table->integer('revisionable_id');
            $table->integer('user_id')->nullable();
            $table->string('key');
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();
            $table->timestamps();
            
            $table->index(['revisionable_id', 'revisionable_type']);
        });
        
        Schema::create('holymanagements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        Schema::create('ethnicmanagements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        Schema::create('positionmanagements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        Schema::create('careermanagements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        Schema::create('levelmanagements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        Schema::create('languagemanagements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
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
        //Schema::dropIfExists('giao_xu');
        Schema::dropIfExists('parish_managements');
        Schema::dropIfExists('revisions');
        Schema::dropIfExists('holymanagements');
        Schema::dropIfExists('ethnicmanagements');
        Schema::dropIfExists('positionmanagements');
        Schema::dropIfExists('careermanagements');
        Schema::dropIfExists('levelmanagements');        
        Schema::dropIfExists('languagemanagements');
    }
}
