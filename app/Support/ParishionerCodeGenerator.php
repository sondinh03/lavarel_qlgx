<?php

namespace App\Support;

use App\Models\Parishioner;
use App\Models\ParishNew;

class ParishionerCodeGenerator
{
    /**
     * Mã giáo dân: {mã xứ}-GD-{năm 2 số}-{thứ tự 4 số}
     * VD: HDO-GD-25-0001 — khác format học sinh (HDO-25-0012).
     */
    public static function generate(?int $parishId): string
    {
        $parishCode = ParishNew::find($parishId)?->code ?? 'GXU';
        $year = substr((string) now()->year, -2);
        $prefix = "{$parishCode}-GD-{$year}-";

        $last = Parishioner::query()
            ->when($parishId, fn ($q) => $q->where('parish_id', $parishId))
            ->where('code', 'like', "{$prefix}%")
            ->max('code');

        $lastNumber = $last
            ? (int) substr($last, strlen($prefix))
            : 0;

        $sequence = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$sequence}";
    }
}
