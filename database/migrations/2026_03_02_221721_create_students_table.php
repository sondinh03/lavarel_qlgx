<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->string('student_code')->unique();

            $table->uuid('qr_token')->unique();

            $table->string('avatar_path')->nullable();

            // Liên kết giáo dân (optional)
            $table->unsignedBigInteger('parishioner_id')->nullable();

            // Thuộc giáo xứ quản lý học vụ
            $table->unsignedBigInteger('parish_id');

            // Tên thánh
            $table->unsignedBigInteger('saint_id')->nullable();

            // Tên đời
            $table->string('first_name');
            $table->string('last_name');

            $table->date('birthday')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('parish_id');
            $table->index('parishioner_id');
            $table->index('saint_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
