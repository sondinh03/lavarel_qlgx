<?php

return [
    /*
    | Legacy DB convention (giữ tương thích export Word / Backpack):
    | sex 0 = bên nữ, sex 1 = bên nam
    */
    'sex' => [
        0 => 'Bên nữ',
        1 => 'Bên nam',
    ],

    'sex_bride'  => 0,
    'sex_groom'  => 1,

    'participant_status' => [
        0 => 'Bình thường',
        1 => 'Có ngăn trở',
    ],

    'status' => [
        0 => 'Đang rao',
        1 => 'Hoàn thành',
        2 => 'Có ngăn trở',
        3 => 'Đã hủy',
    ],

    'status_badges' => [
        0 => 'bg-amber-100 text-amber-800',
        1 => 'bg-emerald-100 text-emerald-800',
        2 => 'bg-red-100 text-red-800',
        3 => 'bg-slate-100 text-slate-600',
    ],

    'min_days_between_announcements' => 7,
];
