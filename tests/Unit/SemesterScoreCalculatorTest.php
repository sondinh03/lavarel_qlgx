<?php

namespace Tests\Unit;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\GradingSetting;
use App\Models\ScoreType;
use App\Models\StudentScore;
use App\Services\SemesterScoreCalculator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class SemesterScoreCalculatorTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
    }

    public function test_semester_average_falls_back_to_academic_only_without_settings(): void
    {
        $this->giveAcademicScore(8);

        $breakdown = $this->breakdownForSemesterOne();

        $this->assertSame(8.0, $breakdown['academic']);
        $this->assertSame(8.0, $breakdown['total']);
    }

    public function test_semester_average_mixes_academic_and_attendance_by_percent(): void
    {
        $this->giveAcademicScore(8);

        // 4 buổi học: 3 có mặt, 1 vắng không phép → 7.5
        $this->giveAttendance(AttendanceSession::TYPE_CLASS, [
            AttendanceRecord::STATUS_PRESENT,
            AttendanceRecord::STATUS_PRESENT,
            AttendanceRecord::STATUS_PRESENT,
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
        ]);

        // 2 buổi lễ: 1 có mặt, 1 vắng có phép (50%) → 7.5
        $this->giveAttendance(AttendanceSession::TYPE_CEREMONY, [
            AttendanceRecord::STATUS_PRESENT,
            AttendanceRecord::STATUS_ABSENT_EXCUSED,
        ]);

        $this->saveSettings([
            'weight_academic'         => 60,
            'weight_class_attendance' => 30,
            'weight_mass_attendance'  => 10,
        ]);

        $breakdown = $this->breakdownForSemesterOne();

        $this->assertSame(8.0, $breakdown['academic']);
        $this->assertSame(7.5, $breakdown['class_attendance']);
        $this->assertSame(7.5, $breakdown['mass_attendance']);

        // 8×0.6 + 7.5×0.3 + 7.5×0.1 = 7.8
        $this->assertSame(7.8, $breakdown['total']);
    }

    public function test_cancelled_and_unchecked_sessions_do_not_count(): void
    {
        $this->giveAcademicScore(10);

        // 1 buổi có mặt tính điểm, 1 buổi bị hủy và 1 buổi chưa điểm danh đều bị bỏ qua
        $this->giveAttendance(AttendanceSession::TYPE_CLASS, [AttendanceRecord::STATUS_PRESENT]);
        $this->giveAttendance(
            AttendanceSession::TYPE_CLASS,
            [AttendanceRecord::STATUS_ABSENT_UNEXCUSED],
            AttendanceSession::STATUS_CANCELLED
        );
        $this->giveAttendance(AttendanceSession::TYPE_CLASS, [null]);

        $this->saveSettings([
            'weight_academic'         => 50,
            'weight_class_attendance' => 50,
            'weight_mass_attendance'  => 0,
        ]);

        $breakdown = $this->breakdownForSemesterOne();

        $this->assertSame(10.0, $breakdown['class_attendance']);
        $this->assertSame(10.0, $breakdown['total']);
    }

    public function test_missing_component_leaves_average_empty(): void
    {
        $this->giveAcademicScore(9);

        $this->saveSettings([
            'weight_academic'         => 70,
            'weight_class_attendance' => 0,
            'weight_mass_attendance'  => 30,
        ]);

        $breakdown = $this->breakdownForSemesterOne();

        $this->assertSame(9.0, $breakdown['academic']);
        $this->assertNull($breakdown['mass_attendance']);
        $this->assertNull($breakdown['total']);
        $this->assertSame(
            [SemesterScoreCalculator::COMPONENT_MASS_ATTENDANCE],
            $breakdown['missing']
        );
    }

    public function test_attendance_weight_zero_ignores_missing_attendance(): void
    {
        $this->giveAcademicScore(9);

        $this->saveSettings([
            'weight_academic'         => 100,
            'weight_class_attendance' => 0,
            'weight_mass_attendance'  => 0,
        ]);

        $this->assertSame(9.0, $this->breakdownForSemesterOne()['total']);
    }

    public function test_year_average_uses_semester_percents(): void
    {
        $settings = new GradingSetting([
            'weight_semester_1' => 30,
            'weight_semester_2' => 70,
        ]);

        $calculator = app(SemesterScoreCalculator::class);

        // 5×0.3 + 10×0.7 = 8.5
        $this->assertSame(8.5, $calculator->yearAverage(5.0, 10.0, $settings));
        $this->assertNull($calculator->yearAverage(5.0, null, $settings));
    }

    public function test_grade_level_settings_override_parish_settings(): void
    {
        $this->giveAcademicScore(6);
        $this->giveAttendance(AttendanceSession::TYPE_CLASS, [AttendanceRecord::STATUS_PRESENT]);

        $this->saveSettings([
            'weight_academic'         => 100,
            'weight_class_attendance' => 0,
            'weight_mass_attendance'  => 0,
        ]);

        $this->saveSettings([
            'weight_academic'         => 50,
            'weight_class_attendance' => 50,
            'weight_mass_attendance'  => 0,
        ], $this->fx->classAssigned->grade_level_id);

        // 6×0.5 + 10×0.5 = 8
        $this->assertSame(8.0, $this->breakdownForSemesterOne()['total']);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function test_inactive_score_types_are_excluded_from_academic_average(): void
    {
        // Cột đang bật: 8 · hệ số 1
        $this->giveAcademicScore(8);

        // Cột đã tắt: 4 · hệ số 1 — nếu vẫn tính vào sẽ kéo TB xuống 6
        $inactive = ScoreType::query()->create([
            'class_id'    => $this->fx->classAssigned->id,
            'semester'    => ScoreType::SEMESTER_1,
            'type'        => ScoreType::TYPE_15_PHUT,
            'name'        => 'Cột đã tắt',
            'order'       => 2,
            'coefficient' => 1,
            'max_score'   => 10,
            'is_active'   => false,
        ]);

        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id'    => $inactive->id,
            'score_value'      => 4,
            'attempt'          => 1,
        ]);

        $breakdown = $this->breakdownForSemesterOne();

        $this->assertSame(8.0, $breakdown['academic']);
        $this->assertSame(8.0, $breakdown['total']);
    }

    public function test_inactive_midterm_does_not_block_academic_average(): void
    {
        $this->giveAcademicScore(9);

        // Giữa kỳ đã tắt và chưa có điểm — không được chặn TB học tập
        ScoreType::query()->create([
            'class_id'    => $this->fx->classAssigned->id,
            'semester'    => ScoreType::SEMESTER_1,
            'type'        => ScoreType::TYPE_GIUA_KY,
            'name'        => 'Giữa kỳ đã tắt',
            'order'       => 3,
            'coefficient' => 2,
            'max_score'   => 10,
            'is_active'   => false,
        ]);

        $breakdown = $this->breakdownForSemesterOne();

        $this->assertSame(9.0, $breakdown['academic']);
        $this->assertSame(9.0, $breakdown['total']);
    }

    private function breakdownForSemesterOne(): array
    {
        return app(SemesterScoreCalculator::class)->forClassSemester(
            $this->fx->classAssigned->id,
            ScoreType::SEMESTER_1
        )[$this->fx->pivotAssigned->id];
    }

    private function saveSettings(array $weights, ?int $gradeLevelId = null): GradingSetting
    {
        return GradingSetting::query()->create(array_merge(GradingSetting::DEFAULTS, $weights, [
            'parish_id'      => $this->fx->parishA->id,
            'school_year_id' => $this->fx->yearA->id,
            'grade_level_id' => $gradeLevelId,
        ]));
    }

    private function giveAcademicScore(float $value): void
    {
        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id'    => $this->fx->scoreTypeAssigned->id,
            'score_value'      => $value,
            'attempt'          => 1,
        ]);
    }

    /** @param  array<int|null>  $statuses  Mỗi phần tử là một buổi; null = chưa điểm danh */
    private function giveAttendance(
        int $type,
        array $statuses,
        int $sessionStatus = AttendanceSession::STATUS_CLOSED
    ): void {
        foreach ($statuses as $index => $status) {
            $session = AttendanceSession::query()->create([
                'class_id'   => $this->fx->classAssigned->id,
                'date'       => now()->subDays($index + 1)->toDateString(),
                'semester'   => ScoreType::SEMESTER_1,
                'type'       => $type,
                'status'     => $sessionStatus,
            ]);

            AttendanceRecord::query()->create([
                'session_id' => $session->id,
                'student_id' => $this->fx->studentAssigned->id,
                'status'     => $status,
            ]);
        }
    }
}
