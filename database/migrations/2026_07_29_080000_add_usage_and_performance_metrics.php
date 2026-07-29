<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_login_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('parish_id')->nullable()->index();
            $table->string('guard', 32)->default('web')->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['created_at', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        Schema::create('system_metric_daily', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date')->unique();
            $table->unsignedInteger('logins')->default(0);
            $table->unsignedInteger('failed_logins')->default(0);
            $table->unsignedInteger('requests')->default(0);
            $table->unsignedInteger('slow_requests')->default(0);
            $table->unsignedInteger('server_errors')->default(0);
            $table->unsignedBigInteger('avg_duration_ms_sum')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_metric_daily');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_login_at');
        });

        Schema::dropIfExists('user_login_events');
    }
};
