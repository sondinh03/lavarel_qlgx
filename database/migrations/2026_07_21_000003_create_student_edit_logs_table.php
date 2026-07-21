<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_edit_logs')) {
            return;
        }

        Schema::create('student_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parish_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_edit_logs');
    }
};
