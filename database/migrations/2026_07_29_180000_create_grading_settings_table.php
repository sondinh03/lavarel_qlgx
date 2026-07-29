<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_settings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parish_id');
            $table->unsignedBigInteger('school_year_id')->nullable()
                ->comment('null = mặc định của giáo xứ cho mọi năm học');
            $table->unsignedBigInteger('grade_level_id')->nullable()
                ->comment('null = áp dụng cho mọi khối');

            $table->decimal('weight_academic', 5, 2)->default(100)
                ->comment('% điểm trung bình học tập trong TB học kỳ');
            $table->decimal('weight_class_attendance', 5, 2)->default(0)
                ->comment('% điểm chuyên cần học');
            $table->decimal('weight_mass_attendance', 5, 2)->default(0)
                ->comment('% điểm chuyên cần lễ');

            $table->decimal('weight_semester_1', 5, 2)->default(50)
                ->comment('% học kỳ 1 trong TB cả năm');
            $table->decimal('weight_semester_2', 5, 2)->default(50)
                ->comment('% học kỳ 2 trong TB cả năm');

            $table->decimal('excused_credit_percent', 5, 2)->default(50)
                ->comment('Vắng có phép được tính bằng bao nhiêu % một buổi có mặt');

            $table->timestamps();

            $table->index(['parish_id', 'school_year_id'], 'idx_grading_parish_year');
            $table->unique(
                ['parish_id', 'school_year_id', 'grade_level_id'],
                'uq_grading_scope'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_settings');
    }
};
