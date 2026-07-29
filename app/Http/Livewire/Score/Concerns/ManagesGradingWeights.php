<?php

namespace App\Http\Livewire\Score\Concerns;

use App\Models\GradingSetting;
use App\Models\ScoreType;
use App\Models\StudentNew;
use App\Services\GradingWeightResolver;
use App\Services\SemesterScoreCalculator;

/**
 * Tab "Cách tính điểm": tỉ lệ giữa các thành phần điểm của học kỳ và cả năm.
 *
 * Cấu hình gắn với năm học và (tuỳ chọn) một khối, nên sửa tỉ lệ năm nay
 * không làm thay đổi điểm của các năm trước.
 */
trait ManagesGradingWeights
{
    /** @var string Phạm vi cài đặt: 'parish' (toàn xứ trong năm học) | 'grade' (riêng một khối) */
    public $weightScope = 'parish';

    /** @var int|null Khối khi weightScope = 'grade' */
    public $weightScopeGradeId = null;

    /** @var float % điểm trung bình học tập trong TB học kỳ */
    public $weightAcademic = 100;

    /** @var float % chuyên cần học */
    public $weightClassAttendance = 0;

    /** @var float % chuyên cần lễ */
    public $weightMassAttendance = 0;

    /** @var float % học kỳ 1 trong TB cả năm */
    public $weightSemester1 = 50;

    /** @var float % học kỳ 2 trong TB cả năm */
    public $weightSemester2 = 50;

    /** @var float Vắng có phép được tính bằng bao nhiêu % một buổi có mặt */
    public $excusedCreditPercent = 50;

    /** @var bool Phạm vi đang chọn đã có cấu hình riêng, hay đang thừa hưởng phạm vi rộng hơn */
    public bool $weightOverrideExists = false;

    /** @var string|null Nguồn cấu hình đang có hiệu lực (để hiển thị) */
    public ?string $weightSourceLabel = null;

    protected $gradingRules = [
        'weightScope'           => 'required|in:parish,grade',
        'weightScopeGradeId'    => 'nullable|integer',
        'weightAcademic'        => 'required|numeric|min:0|max:100',
        'weightClassAttendance' => 'required|numeric|min:0|max:100',
        'weightMassAttendance'  => 'required|numeric|min:0|max:100',
        'weightSemester1'       => 'required|numeric|min:0|max:100',
        'weightSemester2'       => 'required|numeric|min:0|max:100',
        'excusedCreditPercent'  => 'required|numeric|min:0|max:100',
    ];

    protected $gradingMessages = [
        'weightAcademic.required'        => 'Vui lòng nhập tỉ lệ điểm trung bình học tập',
        'weightClassAttendance.required' => 'Vui lòng nhập tỉ lệ chuyên cần học',
        'weightMassAttendance.required'  => 'Vui lòng nhập tỉ lệ chuyên cần lễ',
        'weightSemester1.required'       => 'Vui lòng nhập tỉ lệ học kỳ 1',
        'weightSemester2.required'       => 'Vui lòng nhập tỉ lệ học kỳ 2',
        'excusedCreditPercent.required'  => 'Vui lòng nhập tỉ lệ quy đổi vắng có phép',
    ];

    /** Nạp form cài đặt theo phạm vi đang chọn (toàn xứ trong năm học, hoặc riêng một khối). */
    protected function loadGradingSettingsForm(): void
    {
        $resolver = app(GradingWeightResolver::class);

        if ($this->weightScope === 'grade' && ! $this->weightScopeGradeId) {
            $this->weightScopeGradeId = $this->selectedKhoi;
        }

        $gradeId = $this->weightScope === 'grade' && $this->weightScopeGradeId
            ? (int) $this->weightScopeGradeId
            : null;

        $schoolYearId = $this->selectedNamHoc ? (int) $this->selectedNamHoc : null;

        $exact    = $resolver->findExact($this->parishId, $schoolYearId, $gradeId);
        $settings = $exact ?? $resolver->resolve($this->parishId, $schoolYearId, $gradeId);

        $this->weightOverrideExists  = $exact !== null;
        $this->weightSourceLabel     = $settings->scopeLabel();
        $this->weightAcademic        = (float) $settings->weight_academic;
        $this->weightClassAttendance = (float) $settings->weight_class_attendance;
        $this->weightMassAttendance  = (float) $settings->weight_mass_attendance;
        $this->weightSemester1       = (float) $settings->weight_semester_1;
        $this->weightSemester2       = (float) $settings->weight_semester_2;
        $this->excusedCreditPercent  = (float) $settings->excused_credit_percent;
    }

    /** Cấu hình trọng số gắn với năm học nên phải nạp lại form khi đổi năm/khối. */
    protected function refreshGradingContext(): void
    {
        $this->forgetCalculatedScores();

        if ($this->canManageScoreConfig) {
            $this->loadGradingSettingsForm();
        }
    }

    public function updatedWeightScope(): void
    {
        if (! in_array($this->weightScope, ['parish', 'grade'], true)) {
            $this->weightScope = 'parish';
        }

        $this->resetValidation();
        $this->loadGradingSettingsForm();
    }

    public function updatedWeightScopeGradeId(): void
    {
        $this->weightScopeGradeId = $this->toInt($this->weightScopeGradeId);
        $this->resetValidation();
        $this->loadGradingSettingsForm();
    }

    public function saveGradingSettings(): void
    {
        $this->authorize('create', ScoreType::class);

        if (! $this->parishId) {
            $this->emit('toast', 'error', 'Không xác định được giáo xứ');

            return;
        }

        if (! $this->selectedNamHoc) {
            $this->emit('toast', 'warning', 'Vui lòng chọn năm học trước');

            return;
        }

        $this->validate($this->gradingRules, $this->gradingMessages);

        if ($this->weightScope === 'grade' && ! $this->weightScopeGradeId) {
            $this->addError('weightScopeGradeId', 'Vui lòng chọn khối áp dụng');

            return;
        }

        if (! $this->assertWeightsSumToOneHundred()) {
            return;
        }

        try {
            GradingSetting::updateOrCreate(
                [
                    'parish_id'      => (int) $this->parishId,
                    'school_year_id' => (int) $this->selectedNamHoc,
                    'grade_level_id' => $this->weightScope === 'grade'
                        ? (int) $this->weightScopeGradeId
                        : null,
                ],
                [
                    'weight_academic'         => (float) $this->weightAcademic,
                    'weight_class_attendance' => (float) $this->weightClassAttendance,
                    'weight_mass_attendance'  => (float) $this->weightMassAttendance,
                    'weight_semester_1'       => (float) $this->weightSemester1,
                    'weight_semester_2'       => (float) $this->weightSemester2,
                    'excused_credit_percent'  => (float) $this->excusedCreditPercent,
                ]
            );

            $this->afterGradingSettingsChanged();
            $this->emit('toast', 'message', 'Đã lưu cách tính điểm');
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving grading settings');
            $this->emit('toast', 'error', 'Có lỗi khi lưu cách tính điểm');
        }
    }

    /** Ba tỉ lệ của học kỳ và hai tỉ lệ của cả năm đều phải cộng đúng 100%. */
    protected function assertWeightsSumToOneHundred(): bool
    {
        $componentSum = round(
            (float) $this->weightAcademic
            + (float) $this->weightClassAttendance
            + (float) $this->weightMassAttendance,
            2
        );

        if (abs($componentSum - 100) > 0.01) {
            $this->addError(
                'weightAcademic',
                "Tổng ba tỉ lệ của học kỳ phải bằng 100%, hiện là {$componentSum}%"
            );

            return false;
        }

        $semesterSum = round((float) $this->weightSemester1 + (float) $this->weightSemester2, 2);

        if (abs($semesterSum - 100) > 0.01) {
            $this->addError(
                'weightSemester1',
                "Tổng tỉ lệ hai học kỳ phải bằng 100%, hiện là {$semesterSum}%"
            );

            return false;
        }

        return true;
    }

    /** Xoá cấu hình riêng của phạm vi đang chọn để quay về thừa hưởng phạm vi rộng hơn. */
    public function deleteGradingSettings(): void
    {
        $this->authorize('create', ScoreType::class);

        if (! $this->parishId || ! $this->selectedNamHoc) {
            return;
        }

        try {
            GradingSetting::query()
                ->where('parish_id', (int) $this->parishId)
                ->where('school_year_id', (int) $this->selectedNamHoc)
                ->where(
                    'grade_level_id',
                    $this->weightScope === 'grade' ? (int) $this->weightScopeGradeId : null
                )
                ->delete();

            $this->afterGradingSettingsChanged();
            $this->emit('toast', 'message', 'Đã xoá cấu hình riêng của phạm vi này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting grading settings');
            $this->emit('toast', 'error', 'Có lỗi khi xoá cấu hình');
        }
    }

    protected function afterGradingSettingsChanged(): void
    {
        $this->resolvedGradingSettings = null;
        $this->loadGradingSettingsForm();
        $this->ensureBreakdownsLoaded(true);
        $this->recalculateRatingStats();
    }

    /**
     * Xem trước TB học kỳ của một học sinh thật với tỉ lệ đang nhập trên form
     * (chưa lưu), để quản trị viên kiểm chứng trước khi áp dụng cho cả xứ.
     */
    public function buildGradingPreview(): ?array
    {
        if (! $this->selectedLop) {
            return null;
        }

        $this->ensureBreakdownsLoaded();

        $pivotId = $this->firstPivotIdWithAnyComponent();

        if (! $pivotId) {
            return null;
        }

        $student = StudentNew::query()
            ->join('students_class', 'students.id', '=', 'students_class.student_id')
            ->where('students_class.id', $pivotId)
            ->select('students.first_name', 'students.last_name')
            ->first();

        $calculator = app(SemesterScoreCalculator::class);
        $draft      = $this->draftGradingSetting();

        $combined = $calculator->forClassSemester(
            (int) $this->selectedLop,
            (int) $this->selectedSemester,
            $draft
        )[$pivotId] ?? $calculator->combineComponents($this->scoreBreakdowns[$pivotId], $draft);

        return [
            'student_name' => trim(($student->last_name ?? '') . ' ' . ($student->first_name ?? '')),
            'breakdown'    => $combined,
            'missing'      => $calculator->describeMissing($combined['missing']),
        ];
    }

    /** Học sinh đầu tiên có ít nhất một thành phần điểm để làm ví dụ xem trước. */
    protected function firstPivotIdWithAnyComponent(): ?int
    {
        foreach ($this->scoreBreakdowns as $id => $breakdown) {
            if ($breakdown['academic'] !== null
                || $breakdown['class_attendance'] !== null
                || $breakdown['mass_attendance'] !== null) {
                return (int) $id;
            }
        }

        return null;
    }

    /** Cấu hình tạm từ tỉ lệ đang nhập trên form, không lưu vào database. */
    protected function draftGradingSetting(): GradingSetting
    {
        return new GradingSetting([
            'weight_academic'         => (float) $this->weightAcademic,
            'weight_class_attendance' => (float) $this->weightClassAttendance,
            'weight_mass_attendance'  => (float) $this->weightMassAttendance,
            'weight_semester_1'       => (float) $this->weightSemester1,
            'weight_semester_2'       => (float) $this->weightSemester2,
            'excused_credit_percent'  => (float) $this->excusedCreditPercent,
        ]);
    }
}
