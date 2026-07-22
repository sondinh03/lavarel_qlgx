<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bỏ bảng records gắn nhầm vào attendance_sessions (phiên học sinh)
        Schema::dropIfExists('teacher_attendance_records');

        Schema::create('teacher_attendance_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parish_id')
                ->constrained('parishes')
                ->cascadeOnDelete();

            $table->foreignId('namhoc_id')
                ->constrained('nam_hoc')
                ->cascadeOnDelete();

            $table->date('date');

            // 1: đi dạy, 2: đi lễ, 3: họp
            $table->tinyInteger('type')->default(1);

            // 1: đang mở, 2: đã khóa, 3: hủy
            $table->tinyInteger('status')->default(1);

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('note')->nullable();

            $table->timestamps();

            $table->unique(['parish_id', 'namhoc_id', 'date', 'type'], 'teacher_att_sessions_unique');
            $table->index(['namhoc_id', 'type', 'date']);
            $table->index('status');
        });

        Schema::create('teacher_attendance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->constrained('teacher_attendance_sessions')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            // 1: có mặt, 2: vắng CP, 3: vắng KP
            $table->tinyInteger('status')->nullable();
            $table->string('note')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->unique(['session_id', 'teacher_id']);
            $table->index('teacher_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance_records');
        Schema::dropIfExists('teacher_attendance_sessions');

        // Khôi phục bản gắn attendance_sessions (không khuyến nghị dùng lại)
        Schema::create('teacher_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->tinyInteger('status')->nullable();
            $table->string('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'teacher_id']);
        });
    }
};
