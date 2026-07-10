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
    | Mật khẩu mặc định khi tạo / reset tài khoản giáo lý viên
    |--------------------------------------------------------------------------
    */
    'catechist_default_password' => env('QLGX_CATECHIST_DEFAULT_PASSWORD', '12345678'),
];
