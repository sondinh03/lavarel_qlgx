<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishes', function (Blueprint $table) {
            // Giờ cắt khi đọc số liệu: thiếu record → KP nếu buổi đã có dữ liệu; không tạo record.
            $table->boolean('attendance_auto_finalize_enabled')->default(true)->after('scores_entry_open');
            $table->time('attendance_auto_finalize_time')->default('20:00:00')->after('attendance_auto_finalize_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('parishes', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_auto_finalize_enabled',
                'attendance_auto_finalize_time',
            ]);
        });
    }
};
