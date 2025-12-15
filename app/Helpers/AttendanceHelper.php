<?php

if (!function_exists('attendance_status_badge')) {
    function attendance_status_badge($status)
    {
        $badges = [
            1 => '<span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Có mặt</span>',
            2 => '<span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Vắng CP</span>',
            3 => '<span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">Vắng KP</span>',
        ];

        return $badges[$status] ?? '<span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">-</span>';
    }
}

if (!function_exists('attendance_type_name')) {
    function attendance_type_name($type)
    {
        return $type == 1 ? 'Đi học' : 'Đi lễ';
    }
}

if (!function_exists('session_status_badge')) {
    function session_status_badge($status)
    {
        $badges = [
            1 => '<span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">Đang mở</span>',
            2 => '<span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">Đã đóng</span>',
            3 => '<span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">Đã hủy</span>',
        ];

        return $badges[$status] ?? '';
    }
}
