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
];
