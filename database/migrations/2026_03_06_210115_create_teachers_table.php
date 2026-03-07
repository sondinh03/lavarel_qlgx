<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            // ── Liên kết ──────────────────────────────────────────
            $table->foreignId('parish_id')
                ->constrained('parishes')
                ->cascadeOnDelete();

            $table->foreignId('parish_group_id')
                ->nullable()
                ->constrained('parish_groups')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('saint_id')
                ->nullable()
                ->constrained('holymanagements')
                ->nullOnDelete();

            // ── Họ tên ────────────────────────────────────────────
            $table->string('last_name', 50);
            $table->string('first_name', 50);

            // ── Thông tin cá nhân ─────────────────────────────────
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birthday')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('avatar_path', 500)->nullable();

            // ── Trạng thái ────────────────────────────────────────
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
