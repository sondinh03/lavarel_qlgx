<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('backpack.settings.table_name', 'settings');
        $now = now();

        $rows = [
            [
                'key'         => 'support_phone',
                'name'        => 'Hotline hỗ trợ',
                'description' => 'Số điện thoại hỗ trợ hệ thống (hiển thị trang chủ / đăng nhập).',
                'value'       => '',
                'field'       => json_encode([
                    'name'  => 'value',
                    'label' => 'Số điện thoại',
                    'type'  => 'text',
                ], JSON_UNESCAPED_UNICODE),
                'active'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'         => 'support_email',
                'name'        => 'Email hỗ trợ',
                'description' => 'Email hỗ trợ hệ thống (hiển thị trang chủ / đăng nhập).',
                'value'       => '',
                'field'       => json_encode([
                    'name'  => 'value',
                    'label' => 'Email',
                    'type'  => 'email',
                ], JSON_UNESCAPED_UNICODE),
                'active'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'         => 'support_note',
                'name'        => 'Ghi chú hỗ trợ',
                'description' => 'Ví dụ: giờ làm việc, Zalo, Messenger…',
                'value'       => '',
                'field'       => json_encode([
                    'name'  => 'value',
                    'label' => 'Ghi chú',
                    'type'  => 'textarea',
                ], JSON_UNESCAPED_UNICODE),
                'active'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            $exists = DB::table($table)->where('key', $row['key'])->exists();
            if (! $exists) {
                DB::table($table)->insert($row);
            }
        }
    }

    public function down(): void
    {
        $table = config('backpack.settings.table_name', 'settings');

        DB::table($table)->whereIn('key', [
            'support_phone',
            'support_email',
            'support_note',
        ])->delete();
    }
};
