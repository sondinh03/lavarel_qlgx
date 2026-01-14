<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentClassSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_class_summaries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');

            $table->decimal('avg_hk1', 5, 2)->nullable()
                ->comment('Điểm trung bình học kỳ 1');
            $table->decimal('avg_hk2', 5, 2)->nullable()
                ->comment('Điểm trung bình học kỳ 2');
            $table->decimal('avg_year', 5, 2)->nullable()
                ->comment('Điểm trung bình cả năm');

            $table->string('ranking', 50)->nullable()
                ->comment('Xếp loại: Giỏi, Khá, Trung bình, Yếu');

            $table->tinyInteger('result')->nullable()
                ->comment('1 = Lên lớp, 0 = Ở lại');

            $table->string('note')->nullable();

            $table->timestamps();

            // Index
            $table->index('student_id');
            $table->index('class_id');

            // Unique: Mỗi học sinh + mỗi lớp chỉ có 1 dòng tổng kết
            $table->unique(['student_id', 'class_id'], 'uq_student_class_summary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_class_summaries');
    }
}
