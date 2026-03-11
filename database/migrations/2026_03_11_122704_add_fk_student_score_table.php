<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFkStudentScoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_scores', function (Blueprint $table) {
            // FK score_type_id
            $table->foreign('score_type_id')
                ->references('id')->on('score_types')
                ->onDelete('cascade');

            // UNIQUE: 1 học sinh chỉ có 1 điểm mỗi loại mỗi lần thi
            $table->unique(
                ['student_class_id', 'score_type_id', 'attempt'],
                'uq_student_score_attempt'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropForeign(['score_type_id']);
            $table->dropUnique('uq_student_score_attempt');
        });
    }
}
