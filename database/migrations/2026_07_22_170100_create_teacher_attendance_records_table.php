<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_attendance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->constrained('attendance_sessions')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            // 1: có mặt, 2: vắng có phép, 3: vắng không phép
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
    }
};
