<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parish_id')->index();
            $table->unsignedBigInteger('session_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('attendance_record_id')->nullable()->index();
            $table->unsignedTinyInteger('old_status')->nullable();
            $table->unsignedTinyInteger('new_status')->nullable();
            $table->string('old_note', 500)->nullable();
            $table->string('new_note', 500)->nullable();
            $table->string('action', 20); // created | updated
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('parish_id')->references('id')->on('parishes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_edit_logs');
    }
};
