<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parishioner_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code', 32)->unique();
            $table->foreignId('parish_id')->constrained('parishes')->cascadeOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('submitted_name')->index();
            $table->string('submitted_phone', 20)->nullable()->index();
            $table->json('payload');
            $table->json('sacraments')->nullable();
            $table->string('avatar_path')->nullable();
            $table->foreignId('parishioner_id')->nullable()->constrained('parishioners_new')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parishioner_registration_requests');
    }
};
