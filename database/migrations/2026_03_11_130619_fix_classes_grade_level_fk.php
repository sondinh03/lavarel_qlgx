<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixClassesGradeLevelFk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {

            // drop FK cũ
            $table->dropForeign('classes_grade_level_id_foreign');

            // tạo FK mới
            $table->foreign('grade_level_id')
                ->references('id')
                ->on('grade_levels')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {

            // drop FK mới
            $table->dropForeign(['grade_level_id']);

            // tạo lại FK cũ
            $table->foreign('grade_level_id')
                ->references('id')
                ->on('block')
                ->restrictOnDelete();
        });
    }
}
