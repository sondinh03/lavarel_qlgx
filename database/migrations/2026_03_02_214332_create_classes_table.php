<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            
            // Thuộc giáo xứ nào
            $table->unsignedBigInteger('parish_id');

            // Thuộc năm học nào
            $table->unsignedBigInteger('school_year_id');

            // Thuộc khối nào
            $table->unsignedBigInteger('grade_level_id');

            // Tên lớp (1A, 1B, 2A...)
            $table->string('name');

            // Sức chứa tối đa (optional)
            $table->integer('capacity')->nullable();

            // Trạng thái lớp
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Index
            $table->index('parish_id');
            $table->index('school_year_id');
            $table->index('grade_level_id');

            // Không cho trùng lớp trong cùng năm + cùng khối
            $table->unique(
                ['school_year_id', 'grade_level_id', 'name'],
                'unique_class_per_year_grade'
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
        Schema::dropIfExists('classes');
    }
}
