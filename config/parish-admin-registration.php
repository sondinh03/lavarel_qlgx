<?php

return [
    'statuses' => [
        'pending'  => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ],

    'require_invite' => env('PARISH_ADMIN_REQUIRE_INVITE', false),

    'rate_limit' => [
        'max_attempts' => 5,
        'decay_seconds' => 3600,
    ],
];
