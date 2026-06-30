<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parish_admin_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code', 32)->unique();
            $table->foreignId('parish_id')->constrained('parishes')->cascadeOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone', 20)->nullable();
            $table->string('password_hash');
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parish_admin_registration_requests');
    }
};
