<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiemthiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diemthi', function (Blueprint $table) {
            $table->id();
            $table->integer('ihv');
            $table->integer('lop');
            
            $table->decimal('tuan1')->nullable();
            $table->decimal('k1')->nullable();
            $table->decimal('kinh1')->nullable();
            $table->string('kq1')->nullable();          
            $table->decimal('tuan2')->nullable();
            $table->decimal('k2')->nullable();
            $table->decimal('kinh2')->nullable();
            $table->string('kq2')->nullable();
            
            
            $table->decimal('canam')->nullable();
            $table->string('seploai')->nullable();
            $table->integer('nghile')->nullable();
            $table->integer('bohoc')->nullable();
            $table->string('hanhkiem')->nullable();
            $table->text('ghichu')->nullable();
            
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
        Schema::dropIfExists('diemthi');
    }
}
