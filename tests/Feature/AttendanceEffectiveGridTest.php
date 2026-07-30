<?php

namespace Tests\Feature;

use App\Http\Livewire\AttendanceManager;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ScoreType;
use App\Models\StudentNew;
use App\Models\StudentsClass;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class AttendanceEffectiveGridTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
        $this->fx->parishA->update(['attendance_auto_finalize_time' => '00:00:00']);
    }

    public function test_grid_shows_inferred_kp_and_allows_replacing_it_with_real_status(): void
    {
        $missing = $this->enrollSecondStudent();
        $session = $this->openSessionToday();
        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $this->fx->studentAssigned->id,
            'status' => AttendanceRecord::STATUS_PRESENT,
        ]);
        $key = $missing->id . '_' . $session->id;

        $component = Livewire::actingAs($this->fx->catechismAdmin)
            ->test(AttendanceManager::class)
            ->set('selectedClassId', $this->fx->classAssigned->id)
            ->assertSet("attendanceRecords.{$key}.status", AttendanceRecord::STATUS_ABSENT_UNEXCUSED)
            ->assertSet("attendanceRecords.{$key}.inferred", true);

        $this->assertDatabaseMissing('attendance_records', [
            'session_id' => $session->id,
            'student_id' => $missing->id,
        ]);

        $component
            ->call('saveFromClient', [
                $key => [
                    'status' => AttendanceRecord::STATUS_PRESENT,
                    'note' => '',
                ],
            ])
            ->assertDispatchedBrowserEvent('attendance-saved')
            ->assertSet("attendanceRecords.{$key}.status", AttendanceRecord::STATUS_PRESENT)
            ->assertSet("attendanceRecords.{$key}.inferred", false);

        $this->assertDatabaseHas('attendance_records', [
            'session_id' => $session->id,
            'student_id' => $missing->id,
            'status' => AttendanceRecord::STATUS_PRESENT,
        ]);
    }

    public function test_grid_warns_when_past_cutoff_session_has_no_attendance(): void
    {
        $session = $this->openSessionToday();

        Livewire::actingAs($this->fx->catechismAdmin)
            ->test(AttendanceManager::class)
            ->set('selectedClassId', $this->fx->classAssigned->id)
            ->assertSet('unmarkedConclusiveSessions.0.id', $session->id)
            ->assertSee('Hệ thống không tự tính cả lớp là KP');
    }

    private function openSessionToday(): AttendanceSession
    {
        return AttendanceSession::query()->create([
            'class_id' => $this->fx->classAssigned->id,
            'date' => now()->toDateString(),
            'semester' => ScoreType::SEMESTER_1,
            'type' => AttendanceSession::TYPE_CLASS,
            'status' => AttendanceSession::STATUS_OPENING,
        ]);
    }

    private function enrollSecondStudent(): StudentNew
    {
        $student = StudentNew::query()->create([
            'parish_id' => $this->fx->parishA->id,
            'last_name' => 'Nguyễn Văn',
            'first_name' => 'B',
            'gender' => 'male',
            'is_active' => true,
        ]);

        StudentsClass::query()->create([
            'student_id' => $student->id,
            'class_id' => $this->fx->classAssigned->id,
            'status' => StudentsClass::STATUS_ENROLLED,
        ]);

        return $student;
    }
}
