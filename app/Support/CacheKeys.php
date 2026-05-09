<?php

namespace App\Support;

class CacheKeys
{
    const PARISHES_LIST = 'parishes_list';
    const SAINTS_LIST   = 'saints_list';

    // Có tham số thì dùng method
    public static function parishGroups(int $parishId): string
    {
        return "parish_groups_{$parishId}";
    }
}