<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStudentsClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students_class', function (Blueprint $table) {

            $table->date('enrolled_at')->nullable()->after('status');
            $table->date('left_at')->nullable()->after('enrolled_at');

            $table->unique(['student_id', 'class_id'], 'unique_student_class');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students_class', function (Blueprint $table) {
            //
        });
    }
}
