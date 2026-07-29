<?php

namespace App\Http\Livewire\Score\Concerns;

use App\Models\GradingSetting;
use App\Services\GradingWeightResolver;
use App\Services\SemesterScoreCalculator;

/**
 * Điểm thành phần và TB học kỳ của cả lớp, lấy từ SemesterScoreCalculator.
 *
 * Cũng là nơi duy nhất quyết định bảng điểm hiển thị những cột thành phần nào,
 * để bảng của ban giáo lý và thẻ chi tiết của giáo lý viên không lệch nhau.
 */
trait CalculatesSemesterScores
{
    /** @var array Điểm trung bình học kỳ [student_class_id => float|null] */
    public $averages = [];

    /**
     * Điểm thành phần + TB học kỳ của cả lớp
     * [student_class_id => ['academic', 'class_attendance', 'mass_attendance', 'total', 'missing']]
     *
     * @var array
     */
    public array $scoreBreakdowns = [];

    /** Đã tính TB cho lớp/kỳ hiện tại trong request này chưa (protected nên không giữ qua request). */
    protected bool $breakdownsLoaded = false;

    /** Cấu hình trọng số đang áp dụng cho lớp/bộ lọc hiện tại. */
    protected ?GradingSetting $resolvedGradingSettings = null;

    /** Bỏ TB đã tính của lớp/kỳ cũ để render sau tính lại. */
    protected function forgetCalculatedScores(): void
    {
        $this->resolvedGradingSettings = null;
        $this->breakdownsLoaded        = false;
        $this->scoreBreakdowns         = [];
    }

    /**
     * Tính điểm thành phần + TB học kỳ cho cả lớp qua SemesterScoreCalculator.
     *
     * Tính một lần cho mỗi request: bảng điểm, xếp loại, sort và filter đều
     * cần TB của toàn lớp chứ không riêng trang đang xem.
     */
    protected function ensureBreakdownsLoaded(bool $force = false): void
    {
        if ($this->breakdownsLoaded && ! $force) {
            return;
        }

        $this->breakdownsLoaded = true;

        if (! $this->selectedLop) {
            $this->scoreBreakdowns = [];
            $this->averages        = [];

            return;
        }

        try {
            $this->scoreBreakdowns = app(SemesterScoreCalculator::class)
                ->forClassSemester((int) $this->selectedLop, (int) $this->selectedSemester);

            $this->averages = array_map(
                fn (array $breakdown) => $breakdown['total'],
                $this->scoreBreakdowns
            );
        } catch (\Exception $e) {
            $this->logError($e, 'Error calculating semester averages');
            $this->scoreBreakdowns = [];
            $this->averages        = [];
        }
    }

    /** Cấu hình trọng số đang áp dụng cho lớp (hoặc bộ lọc) hiện tại. */
    public function gradingSettings(): GradingSetting
    {
        if ($this->resolvedGradingSettings !== null) {
            return $this->resolvedGradingSettings;
        }

        $resolver = app(GradingWeightResolver::class);

        return $this->resolvedGradingSettings = $this->selectedLop
            ? $resolver->forClass((int) $this->selectedLop)
            : $resolver->resolve(
                $this->parishId,
                $this->selectedNamHoc ? (int) $this->selectedNamHoc : null,
                $this->selectedKhoi ? (int) $this->selectedKhoi : null
            );
    }

    public function getBreakdown(int $studentClassId): array
    {
        return $this->scoreBreakdowns[$studentClassId] ?? [
            'academic'         => null,
            'class_attendance' => null,
            'mass_attendance'  => null,
            'total'            => null,
            'missing'          => [],
        ];
    }

    public function getAverage(int $studentClassId): ?float
    {
        return $this->averages[$studentClassId] ?? null;
    }

    /** Lý do chưa có TB, ví dụ "Chưa có điểm trung bình học tập, chuyên cần lễ". */
    public function getMissingReason(int $studentClassId): ?string
    {
        return app(SemesterScoreCalculator::class)
            ->describeMissing($this->getBreakdown($studentClassId)['missing'] ?? []);
    }

    /**
     * Cột thành phần nào cần hiện trên bảng điểm.
     *
     * Chuyên cần chỉ hiện khi có trọng số. Cột trung bình học tập chỉ cần thiết
     * khi TB kỳ còn thành phần khác, nếu không nó bằng đúng cột điểm trung bình.
     *
     * @return array{academic: bool, class_attendance: bool, mass_attendance: bool}
     */
    public function columnVisibility(): array
    {
        $settings = $this->gradingSettings();

        $classAttendance = (float) $settings->weight_class_attendance > 0;
        $massAttendance  = (float) $settings->weight_mass_attendance > 0;

        return [
            'academic'         => $classAttendance || $massAttendance,
            'class_attendance' => $classAttendance,
            'mass_attendance'  => $massAttendance,
        ];
    }

    /** Công thức TB học kỳ dạng chữ, ví dụ "trung bình học tập 80% + chuyên cần học 20%". */
    public function averageFormula(): string
    {
        $settings = $this->gradingSettings();
        $columns  = $this->columnVisibility();

        return collect([
            'trung bình học tập ' . $this->weightLabel($settings->weight_academic) . '%',
            $columns['class_attendance']
                ? 'chuyên cần học ' . $this->weightLabel($settings->weight_class_attendance) . '%'
                : null,
            $columns['mass_attendance']
                ? 'chuyên cần lễ ' . $this->weightLabel($settings->weight_mass_attendance) . '%'
                : null,
        ])->filter()->implode(' + ');
    }

    /** Tỉ lệ dạng gọn: 80 thay vì 80,00 và 12,5 thay vì 12,50. */
    public function weightLabel($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, ',', ''), '0'), ',');
    }
}
