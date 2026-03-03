<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStudentClassSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_class_summaries', function (Blueprint $table) {

            // 1️⃣ Thêm cột mới trước
            $table->unsignedBigInteger('student_class_id')->nullable()->after('id');
            $table->tinyInteger('semester')->nullable()->after('student_class_id');
            $table->decimal('average', 5, 2)->nullable()->after('semester');
        });

        // 2️⃣ Xóa các cột cũ
        Schema::table('student_class_summaries', function (Blueprint $table) {

            $table->dropColumn([
                'student_id',
                'class_id',
                'avg_hk1',
                'avg_hk2',
                'avg_year'
            ]);
        });

        // 3️⃣ Thêm unique + foreign key
        Schema::table('student_class_summaries', function (Blueprint $table) {

            $table->foreign('student_class_id')
                ->references('id')
                ->on('students_class')
                ->cascadeOnDelete();

            $table->unique(['student_class_id', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_class_summaries', function (Blueprint $table) {

            $table->dropForeign(['student_class_id']);
            $table->dropUnique(['student_class_id', 'semester']);

            $table->dropColumn([
                'student_class_id',
                'semester',
                'average'
            ]);

            // phục hồi lại cột cũ nếu rollback
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->decimal('avg_hk1', 5, 2)->nullable();
            $table->decimal('avg_hk2', 5, 2)->nullable();
            $table->decimal('avg_year', 5, 2)->nullable();
        });
    }
}
