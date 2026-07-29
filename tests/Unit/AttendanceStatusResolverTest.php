<?php

namespace Tests\Unit;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ParishNew;
use App\Models\ScoreType;
use App\Models\StudentNew;
use App\Models\StudentsClass;
use App\Services\AttendanceStatusResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class AttendanceStatusResolverTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
        $this->fx->parishA->update([
            'attendance_auto_finalize_enabled' => true,
            'attendance_auto_finalize_time'    => '20:00:00',
        ]);
    }

    public function test_missing_student_changes_from_unmarked_to_inferred_unexcused_at_cutoff(): void
    {
        $session = $this->openSessionToday();
        $missing = $this->enrollSecondStudent();
        $this->markAssignedStudentPresent($session);
        $resolver = app(AttendanceStatusResolver::class);

        $before = $resolver->matrix(
            collect([$session->fresh()]),
            null,
            Carbon::today()->setTime(19, 59)
        );
        $after = $resolver->matrix(
            collect([$session->fresh()]),
            null,
            Carbon::today()->setTime(20, 0)
        );

        $this->assertNull($before[$session->id][$missing->id]);
        $this->assertSame(
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
            $after[$session->id][$missing->id]
        );
        $this->assertDatabaseMissing('attendance_records', [
            'session_id' => $session->id,
            'student_id' => $missing->id,
        ]);
        $this->assertSame(AttendanceSession::STATUS_OPENING, (int) $session->fresh()->status);
    }

    public function test_closed_session_concludes_early_without_creating_records(): void
    {
        $session = $this->openSessionToday();
        $missing = $this->enrollSecondStudent();
        $this->markAssignedStudentPresent($session);
        $session->update(['status' => AttendanceSession::STATUS_CLOSED]);

        $matrix = app(AttendanceStatusResolver::class)->matrix(
            collect([$session->fresh()]),
            null,
            Carbon::today()->setTime(10, 0)
        );

        $this->assertSame(
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
            $matrix[$session->id][$missing->id]
        );
        $this->assertDatabaseMissing('attendance_records', [
            'session_id' => $session->id,
            'student_id' => $missing->id,
        ]);
    }

    public function test_session_without_any_attendance_never_infers_absence(): void
    {
        $session = $this->openSessionToday();
        $missing = $this->enrollSecondStudent();
        $session->update(['status' => AttendanceSession::STATUS_CLOSED]);

        $matrix = app(AttendanceStatusResolver::class)->matrix(
            collect([$session->fresh()]),
            null,
            Carbon::today()->setTime(21, 0)
        );

        $this->assertNull($matrix[$session->id][$this->fx->studentAssigned->id]);
        $this->assertNull($matrix[$session->id][$missing->id]);
    }

    public function test_explicit_status_always_wins_over_inference(): void
    {
        $session = $this->openSessionToday();
        $excused = $this->enrollSecondStudent();
        $this->markAssignedStudentPresent($session);
        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $excused->id,
            'status'     => AttendanceRecord::STATUS_ABSENT_EXCUSED,
        ]);

        $matrix = app(AttendanceStatusResolver::class)->matrix(
            collect([$session->fresh()]),
            null,
            Carbon::today()->setTime(21, 0)
        );

        $this->assertSame(
            AttendanceRecord::STATUS_ABSENT_EXCUSED,
            $matrix[$session->id][$excused->id]
        );
    }

    public function test_disabled_cutoff_only_concludes_when_session_is_closed(): void
    {
        $this->fx->parishA->update(['attendance_auto_finalize_enabled' => false]);
        $session = $this->openSessionToday();
        $missing = $this->enrollSecondStudent();
        $this->markAssignedStudentPresent($session);
        $resolver = app(AttendanceStatusResolver::class);

        $open = $resolver->matrix(
            collect([$session->fresh()]),
            null,
            Carbon::today()->setTime(21, 0)
        );
        $session->update(['status' => AttendanceSession::STATUS_CLOSED]);
        $closed = $resolver->matrix(
            collect([$session->fresh()]),
            null,
            Carbon::today()->setTime(21, 0)
        );

        $this->assertNull($open[$session->id][$missing->id]);
        $this->assertSame(
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
            $closed[$session->id][$missing->id]
        );
    }

    public function test_parish_default_cutoff_time_is_20_00(): void
    {
        $this->assertSame('20:00', (new ParishNew())->attendanceAutoFinalizeTimeHi());
    }

    private function openSessionToday(): AttendanceSession
    {
        return AttendanceSession::query()->create([
            'class_id' => $this->fx->classAssigned->id,
            'date'     => now()->toDateString(),
            'semester' => ScoreType::SEMESTER_1,
            'type'     => AttendanceSession::TYPE_CLASS,
            'status'   => AttendanceSession::STATUS_OPENING,
        ]);
    }

    private function enrollSecondStudent(): StudentNew
    {
        $student = StudentNew::query()->create([
            'parish_id'  => $this->fx->parishA->id,
            'last_name'  => 'Nguyễn Văn',
            'first_name' => 'B',
            'gender'     => 'male',
            'is_active'  => true,
        ]);

        StudentsClass::query()->create([
            'student_id' => $student->id,
            'class_id'   => $this->fx->classAssigned->id,
            'status'     => StudentsClass::STATUS_ENROLLED,
        ]);

        return $student;
    }

    private function markAssignedStudentPresent(AttendanceSession $session): void
    {
        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $this->fx->studentAssigned->id,
            'status'     => AttendanceRecord::STATUS_PRESENT,
        ]);
    }
}
