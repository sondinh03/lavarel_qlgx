<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_scores', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('score_type_id');

            $table->decimal('score_value', 5, 2)
                ->comment('Điểm số học sinh đạt được');
            $table->tinyInteger('attempt')->default(1)
                ->comment('Lần nhập điểm (1, 2, 3... nếu có thi lại)');

            $table->string('note')->nullable();

            $table->timestamps();

            // Index quan trọng cho performance
            $table->index('student_id');
            $table->index('class_id');
            $table->index('score_type_id');
            $table->index(['student_id', 'class_id']);
            $table->index(['class_id', 'score_type_id']);
            $table->index(['student_id', 'score_type_id']);
            $table->index(['student_id', 'score_type_id', 'attempt']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_scores');
    }
}
