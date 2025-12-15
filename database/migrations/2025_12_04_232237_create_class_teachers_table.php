<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('namhoc_id');
            $table->tinyInteger('role')->default(1); // 1: chu_nhiem, 2: giao_vien_bo_mon, 3: phu_trach...
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index('teacher_id');
            $table->index('class_id');
            $table->index('namhoc_id');
            $table->index(['class_id', 'namhoc_id']);
            $table->index(['teacher_id', 'namhoc_id']);

            $table->unique(['teacher_id', 'class_id', 'namhoc_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_teachers');
    }
}
