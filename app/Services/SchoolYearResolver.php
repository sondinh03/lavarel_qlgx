<?php

namespace App\Services;

use App\Models\NamHoc;
use App\Support\OperatingSchoolYear;
use Carbon\Carbon;

class SchoolYearResolver
{
    public function resolve(?int $parishId, ?Carbon $on = null): ?OperatingSchoolYear
    {
        if (! $parishId) {
            return null;
        }

        $today = ($on ?? now())->copy()->startOfDay();
        $todayStr = $today->toDateString();

        $activeYears = NamHoc::query()
            ->ofParish($parishId)
            ->active()
            ->orderByDesc('start_date_one')
            ->get();

        if ($activeYears->isEmpty()) {
            return null;
        }

        // 1) Active + today trong [start_date_one, end_date_two]
        $inRange = $activeYears->first(function (NamHoc $year) use ($todayStr) {
            if (! $year->start_date_one || ! $year->end_date_two) {
                return false;
            }

            return $todayStr >= $year->start_date_one->toDateString()
                && $todayStr <= $year->end_date_two->toDateString();
        });

        if ($inRange) {
            return new OperatingSchoolYear(
                $inRange,
                $this->phaseWithinAcademicSpan($inRange, $todayStr),
                $this->semesterWithinAcademicSpan($inRange, $todayStr),
            );
        }

        // 2) Active với start_date_one <= today (năm đã "bắt đầu"), ưu tiên mới nhất
        $started = $activeYears->first(function (NamHoc $year) use ($todayStr) {
            return $year->start_date_one
                && $todayStr >= $year->start_date_one->toDateString();
        });

        if ($started) {
            // Đã qua end_date_two → hè ké năm này
            return new OperatingSchoolYear(
                $started,
                OperatingSchoolYear::PHASE_SUMMER,
                null,
            );
        }

        // 3) Năm mới active nhưng chưa tới start_date_one → ke năm active đã kết thúc gần nhất
        $endedSummer = $activeYears
            ->filter(function (NamHoc $year) use ($todayStr) {
                return $year->end_date_two
                    && $todayStr > $year->end_date_two->toDateString();
            })
            ->sortByDesc(fn (NamHoc $year) => $year->end_date_two?->toDateString())
            ->first();

        if ($endedSummer) {
            return new OperatingSchoolYear(
                $endedSummer,
                OperatingSchoolYear::PHASE_SUMMER,
                null,
            );
        }

        return null;
    }

    public function resolveId(?int $parishId, ?Carbon $on = null): ?int
    {
        return $this->resolve($parishId, $on)?->id();
    }

    /**
     * Học kỳ cho một ngày cụ thể trong năm đã chọn (tạo phiên điểm danh).
     * Ngoài HK1/HK2 (hè / nghỉ giữa kỳ) → null.
     */
    public function semesterForDate(NamHoc $namHoc, Carbon|string $date): ?int
    {
        $carbon = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();
        $day = $carbon->toDateString();

        if (
            $namHoc->start_date_one && $namHoc->end_date_one
            && $day >= $namHoc->start_date_one->toDateString()
            && $day <= $namHoc->end_date_one->toDateString()
        ) {
            return 1;
        }

        if (
            $namHoc->start_date_two && $namHoc->end_date_two
            && $day >= $namHoc->start_date_two->toDateString()
            && $day <= $namHoc->end_date_two->toDateString()
        ) {
            return 2;
        }

        return null;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public function applySessionPhaseFilter($query, OperatingSchoolYear $operating): void
    {
        $namHoc = $operating->namHoc;

        if ($operating->semester === 1 || $operating->semester === 2) {
            $query->where('semester', $operating->semester);

            return;
        }

        // Hè / nghỉ giữa kỳ: phiên ngoài HK1–HK2 (semester null) hoặc ngày ngoài khoảng kỳ
        $query->where(function ($q) use ($namHoc, $operating) {
            $q->whereNull('semester');

            if ($operating->isSummer() && $namHoc->end_date_two) {
                $q->orWhereDate('date', '>', $namHoc->end_date_two->toDateString());
            }

            if ($operating->isBetweenSemesters()
                && $namHoc->end_date_one
                && $namHoc->start_date_two
            ) {
                $q->orWhere(function ($inner) use ($namHoc) {
                    $inner->whereDate('date', '>', $namHoc->end_date_one->toDateString())
                        ->whereDate('date', '<', $namHoc->start_date_two->toDateString());
                });
            }
        });
    }

    protected function phaseWithinAcademicSpan(NamHoc $year, string $todayStr): string
    {
        if (
            $year->start_date_one && $year->end_date_one
            && $todayStr >= $year->start_date_one->toDateString()
            && $todayStr <= $year->end_date_one->toDateString()
        ) {
            return OperatingSchoolYear::PHASE_SEMESTER_1;
        }

        if (
            $year->start_date_two && $year->end_date_two
            && $todayStr >= $year->start_date_two->toDateString()
            && $todayStr <= $year->end_date_two->toDateString()
        ) {
            return OperatingSchoolYear::PHASE_SEMESTER_2;
        }

        return OperatingSchoolYear::PHASE_BETWEEN;
    }

    protected function semesterWithinAcademicSpan(NamHoc $year, string $todayStr): ?int
    {
        return match ($this->phaseWithinAcademicSpan($year, $todayStr)) {
            OperatingSchoolYear::PHASE_SEMESTER_1 => 1,
            OperatingSchoolYear::PHASE_SEMESTER_2 => 2,
            default => null,
        };
    }
}
