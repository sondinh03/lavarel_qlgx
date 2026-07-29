<?php

if (!function_exists('attendance_status_badge')) {
    function attendance_status_badge($status)
    {
        $badges = [
            1 => '<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-emerald-50/90 text-emerald-700 ring-1 ring-emerald-100/80">Có mặt</span>',
            2 => '<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-amber-50/90 text-amber-700 ring-1 ring-amber-100/80">Vắng CP</span>',
            3 => '<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-50/90 text-red-600 ring-1 ring-red-100/80">Vắng KP</span>',
        ];

        return $badges[$status] ?? '<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-50/90 text-slate-500 ring-1 ring-slate-200/60">-</span>';
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
            1 => '<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-emerald-50/90 text-emerald-700 ring-1 ring-emerald-100/80">Hoạt động</span>',
            2 => '<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100/90 text-slate-600 ring-1 ring-slate-200/70">Đã khóa</span>',
            3 => '<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-50/90 text-red-600 ring-1 ring-red-100/80">Đã hủy</span>',
        ];

        return $badges[$status] ?? '';
    }
}
