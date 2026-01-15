<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnStudentScoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_scores', function (Blueprint $table) {
            /**
             * 1. DROP INDEX CŨ
             */
            $table->dropIndex(['student_id']);
            $table->dropIndex(['class_id']);
            $table->dropIndex(['student_id', 'class_id']);
            $table->dropIndex(['class_id', 'score_type_id']);
            $table->dropIndex(['student_id', 'score_type_id']);
            $table->dropIndex(['student_id', 'score_type_id', 'attempt']);

            /**
             * 2. DROP COLUMN CŨ
             */
            $table->dropColumn(['student_id', 'class_id']);

            /**
             * 3. ADD student_class_id (sau id)
             */
            $table->unsignedBigInteger('student_class_id')
                ->after('id')
                ->change();

            /**
             * 4. INDEX MỚI (CHUẨN NGHIỆP VỤ)
             */
            $table->index('student_class_id');
            $table->index(['student_class_id', 'score_type_id']);
            $table->index(['student_class_id', 'score_type_id', 'attempt']);

            /**
             * 5. FOREIGN KEY
             */
            $table->foreign('student_class_id')
                ->references('id')
                ->on('students_class')
                ->onDelete('cascade');
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
            /**
             * DROP FK + INDEX MỚI
             */
            $table->dropForeign(['student_class_id']);
            $table->dropIndex(['student_class_id']);
            $table->dropIndex(['student_class_id', 'score_type_id']);
            $table->dropIndex(['student_class_id', 'score_type_id', 'attempt']);

            $table->dropColumn('student_class_id');

            /**
             * PHỤC HỒI CỘT CŨ
             */
            $table->unsignedBigInteger('student_id')->after('id');
            $table->unsignedBigInteger('class_id')->after('student_id');

            /**
             * PHỤC HỒI INDEX CŨ
             */
            $table->index('student_id');
            $table->index('class_id');
            $table->index(['student_id', 'class_id']);
            $table->index(['class_id', 'score_type_id']);
            $table->index(['student_id', 'score_type_id']);
            $table->index(['student_id', 'score_type_id', 'attempt']);
        });
    }
}
