<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email giả cho tài khoản đăng nhập bằng SĐT (giáo lý viên)
    |--------------------------------------------------------------------------
    */
    'phone_login_domain' => env('QLGX_PHONE_LOGIN_DOMAIN', 'giaoly.local'),

    /*
    |--------------------------------------------------------------------------
    | Mật khẩu dự phòng khi tạo / reset tài khoản giáo lý viên
    |--------------------------------------------------------------------------
    | Mặc định ưu tiên chuỗi ngày sinh (ddmmyyyy). Giá trị này chỉ dùng khi
    | hồ sơ không có ngày sinh.
    */
    'catechist_default_password' => env('QLGX_CATECHIST_DEFAULT_PASSWORD', '12345678'),

    /*
    |--------------------------------------------------------------------------
    | In thẻ / xuất PDF (học sinh & giáo lý viên)
    |--------------------------------------------------------------------------
    | Tạm tắt khi đang chỉnh layout khoảng cách thẻ giữa các trang.
    | Bật lại: QLGX_PRINT_CARDS_ENABLED=true
    */
    'print_cards' => [
        'enabled' => filter_var(env('QLGX_PRINT_CARDS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'maintenance_message' => env(
            'QLGX_PRINT_CARDS_MAINTENANCE_MESSAGE',
            'Chức năng In thẻ / xuất PDF đang bảo trì để chỉnh khoảng cách các thẻ giữa các trang cho đều nhau. Vui lòng quay lại sau.'
        ),
    ],
];
