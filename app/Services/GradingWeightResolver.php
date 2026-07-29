<?php

namespace App\Services;

use App\Models\CatechismClass;
use App\Models\GradingSetting;

/**
 * Tìm cấu hình trọng số điểm áp dụng cho một lớp.
 *
 * Thứ tự ưu tiên: khối trong năm học → toàn xứ trong năm học → khối mọi năm →
 * toàn xứ mọi năm → mặc định hệ thống (100% điểm TB học tập, cả năm chia đôi hai kỳ).
 */
class GradingWeightResolver
{
    /** @var array<string, GradingSetting> */
    private array $cache = [];

    public function forClass(CatechismClass|int|null $class): GradingSetting
    {
        if (is_int($class)) {
            $class = CatechismClass::query()->find($class);
        }

        if (! $class) {
            return GradingSetting::makeDefault();
        }

        return $this->resolve(
            $class->parish_id ? (int) $class->parish_id : null,
            $class->school_year_id ? (int) $class->school_year_id : null,
            $class->grade_level_id ? (int) $class->grade_level_id : null,
        );
    }

    public function resolve(?int $parishId, ?int $schoolYearId, ?int $gradeLevelId = null): GradingSetting
    {
        if (! $parishId) {
            return GradingSetting::makeDefault(null, $schoolYearId);
        }

        $key = $parishId . ':' . ($schoolYearId ?? 0) . ':' . ($gradeLevelId ?? 0);

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $candidates = GradingSetting::query()
            ->where('parish_id', $parishId)
            ->where(function ($query) use ($schoolYearId) {
                $query->whereNull('school_year_id');

                if ($schoolYearId) {
                    $query->orWhere('school_year_id', $schoolYearId);
                }
            })
            ->where(function ($query) use ($gradeLevelId) {
                $query->whereNull('grade_level_id');

                if ($gradeLevelId) {
                    $query->orWhere('grade_level_id', $gradeLevelId);
                }
            })
            ->get();

        $best = $candidates
            ->sortByDesc(fn (GradingSetting $row) => ($row->school_year_id ? 2 : 0) + ($row->grade_level_id ? 1 : 0))
            ->first();

        return $this->cache[$key] = $best ?? GradingSetting::makeDefault($parishId, $schoolYearId);
    }

    /**
     * Bản ghi lưu đúng phạm vi này, không fallback — dùng cho form cài đặt
     * để biết đang sửa cấu hình riêng hay đang thừa hưởng cấu hình rộng hơn.
     */
    public function findExact(?int $parishId, ?int $schoolYearId, ?int $gradeLevelId = null): ?GradingSetting
    {
        if (! $parishId) {
            return null;
        }

        return GradingSetting::query()
            ->where('parish_id', $parishId)
            ->where('school_year_id', $schoolYearId)
            ->where('grade_level_id', $gradeLevelId)
            ->first();
    }

    public function flush(): void
    {
        $this->cache = [];
    }
}
