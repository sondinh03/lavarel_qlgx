<?php

namespace Tests\Unit;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ParishNew;
use App\Models\ScoreType;
use App\Models\StudentNew;
use App\Models\StudentsClass;
use App\Services\AttendanceFinalizer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class AttendanceFinalizerTest extends TestCase
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

    public function test_finalize_marks_missing_students_unexcused_and_closes_session(): void
    {
        $session = $this->openSessionToday();
        $other = $this->enrollSecondStudent();

        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $this->fx->studentAssigned->id,
            'status'     => AttendanceRecord::STATUS_PRESENT,
            'note'       => '',
        ]);

        $result = app(AttendanceFinalizer::class)->finalizeSession($session->fresh());

        $this->assertTrue($result['closed']);
        $this->assertSame(1, $result['marked_absent']);

        $this->assertDatabaseHas('attendance_records', [
            'session_id' => $session->id,
            'student_id' => $other->id,
            'status'     => AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
            'note'       => AttendanceFinalizer::AUTO_NOTE,
        ]);

        $this->assertSame(
            AttendanceSession::STATUS_CLOSED,
            (int) $session->fresh()->status
        );
    }

    public function test_empty_session_is_not_finalized(): void
    {
        $session = $this->openSessionToday();

        $result = app(AttendanceFinalizer::class)->finalizeSession($session);

        $this->assertFalse($result['closed']);
        $this->assertTrue($result['skipped_empty']);
        $this->assertSame(
            AttendanceSession::STATUS_OPENING,
            (int) $session->fresh()->status
        );
        $this->assertSame(0, AttendanceRecord::query()->where('session_id', $session->id)->count());
    }

    public function test_parish_finalize_waits_until_configured_time(): void
    {
        $session = $this->openSessionToday();

        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $this->fx->studentAssigned->id,
            'status'     => AttendanceRecord::STATUS_PRESENT,
        ]);

        $before = Carbon::today()->setTime(19, 59, 0);
        $resultBefore = app(AttendanceFinalizer::class)
            ->finalizeParish($this->fx->parishA->fresh(), $before);

        $this->assertSame(0, $resultBefore['sessions']);
        $this->assertSame(AttendanceSession::STATUS_OPENING, (int) $session->fresh()->status);

        $after = Carbon::today()->setTime(20, 0, 0);
        $resultAfter = app(AttendanceFinalizer::class)
            ->finalizeParish($this->fx->parishA->fresh(), $after);

        $this->assertSame(1, $resultAfter['sessions']);
        $this->assertSame(AttendanceSession::STATUS_CLOSED, (int) $session->fresh()->status);
    }

    public function test_past_open_session_finalizes_even_before_today_cutoff(): void
    {
        $session = AttendanceSession::query()->create([
            'class_id' => $this->fx->classAssigned->id,
            'date'     => now()->subDay()->toDateString(),
            'semester' => ScoreType::SEMESTER_1,
            'type'     => AttendanceSession::TYPE_CLASS,
            'status'   => AttendanceSession::STATUS_OPENING,
        ]);

        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $this->fx->studentAssigned->id,
            'status'     => AttendanceRecord::STATUS_PRESENT,
        ]);

        $beforeCutoff = Carbon::today()->setTime(10, 0, 0);
        $result = app(AttendanceFinalizer::class)
            ->finalizeParish($this->fx->parishA->fresh(), $beforeCutoff);

        $this->assertSame(1, $result['sessions']);
        $this->assertSame(AttendanceSession::STATUS_CLOSED, (int) $session->fresh()->status);
    }

    public function test_parish_default_finalize_time_is_20_00(): void
    {
        $parish = new ParishNew();
        $this->assertSame('20:00', $parish->attendanceAutoFinalizeTimeHi());
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
}
