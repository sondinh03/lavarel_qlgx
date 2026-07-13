<?php

return [
    'statuses' => [
        'pending'  => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ],

    'roles' => [
        'parish_admin'      => [
            'label'       => 'Quản trị xứ',
            'description' => 'Toàn quyền cả giáo dân và giáo lý',
        ],
        'parishioner_admin' => [
            'label'       => 'Quản trị giáo dân',
            'description' => 'Hồ sơ giáo dân, gia đình, bí tích, hôn phối',
        ],
        'catechism_admin'   => [
            'label'       => 'Quản trị giáo lý',
            'description' => 'Lớp học, học sinh, điểm số, điểm danh',
        ],
    ],

    'require_invite' => env('PARISH_ADMIN_REQUIRE_INVITE', false),

    'rate_limit' => [
        'max_attempts' => 5,
        'decay_seconds' => 3600,
    ],
];
