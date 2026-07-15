<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_edit_logs', function (Blueprint $table) {
            $table->uuid('batch_id')->nullable()->after('id')->index();
        });

        // Log cũ: mỗi dòng 1 batch riêng (không gộp giả).
        DB::table('attendance_edit_logs')
            ->whereNull('batch_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('attendance_edit_logs')
                        ->where('id', $row->id)
                        ->update(['batch_id' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('attendance_edit_logs', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};
