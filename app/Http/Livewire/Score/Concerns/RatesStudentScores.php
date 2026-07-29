<?php

namespace App\Http\Livewire\Score\Concerns;

use App\Support\StudentRating;

/**
 * Xếp loại học lực và thống kê theo xếp loại của lớp đang xem.
 *
 * Thang xếp loại nằm ở App\Support\StudentRating để bảng điểm, thống kê và
 * file Excel dùng chung một ngưỡng.
 */
trait RatesStudentScores
{
    /** @var string|null Xếp loại đang lọc */
    public $filterByRating = null;

    /** @var array Thống kê học sinh theo xếp loại */
    public $ratingStats = [];

    /**
     * Thang xếp loại học lực để hiển thị ở tab cách tính điểm.
     *
     * @return array<int, array{key: string, label: string, badge: string, dot: string, range: string}>
     */
    public function ratingScale(): array
    {
        return StudentRating::scale();
    }

    protected function getStudentRating(?float $average): ?string
    {
        return StudentRating::keyFor($average);
    }

    public function updatedFilterByRating(): void
    {
        $this->resetPage();
        $this->recalculateRatingStats();
    }

    public function setFilterByRating(?string $rating): void
    {
        $this->filterByRating = $rating;
    }

    public function clearRatingFilter(): void
    {
        $this->filterByRating = null;
    }

    public function getRatingStats(): array
    {
        return $this->ratingStats;
    }

    /**
     * Thống kê xếp loại cho cả lớp (không chỉ trang hiện tại).
     */
    protected function recalculateRatingStats(): void
    {
        if (! $this->selectedLop) {
            $this->ratingStats = [];
            return;
        }

        $this->ensureBreakdownsLoaded();

        if ($this->averages === []) {
            $this->ratingStats = [];
            return;
        }

        $this->ratingStats = [];
        $totalStudents     = 0;
        $statsByRating     = [];

        foreach ($this->averages as $avg) {
            if ($avg === null) {
                continue;
            }

            $rating = $this->getStudentRating((float) $avg);
            if ($rating) {
                $statsByRating[$rating] = ($statsByRating[$rating] ?? 0) + 1;
                $totalStudents++;
            }
        }

        foreach (StudentRating::scale() as $level) {
            $count = $statsByRating[$level['key']] ?? 0;

            $this->ratingStats[$level['key']] = [
                'label'      => $level['label'],
                'badge'      => $level['badge'],
                'range'      => $level['range'],
                'count'      => $count,
                'percentage' => $totalStudents > 0
                    ? round(($count / $totalStudents) * 100, 1)
                    : 0,
            ];
        }
    }
}
