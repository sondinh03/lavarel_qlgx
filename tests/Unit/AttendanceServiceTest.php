<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AttendanceService;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\StudentNew;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AttendanceServiceTest extends TestCase
{
    use DatabaseTransactions;

    private AttendanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AttendanceService::class);
    }

    public function test_luu_diem_danh_vao_db(): void
    {
        $user    = User::first();
        $session = AttendanceSession::factory()->open()->create();
        $student = StudentNew::first();

        $this->actingAs($user);

        $result = $this->service->saveBulkAttendance([[
            'student_id'     => $student->id,
            'session_id'     => $session->id,
            'status'         => 1,
            'note'           => '',
            'attendanceType' => 1,
        ]]);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $student->id,
            'session_id' => $session->id,
            'status'     => 1,
        ]);
    }

    public function test_luu_2_lan_khong_bi_trung(): void
    {
        $user    = User::first();
        $session = AttendanceSession::factory()->open()->create();
        $student = StudentNew::first();

        $this->actingAs($user);

        $draft = [[
            'student_id'     => $student->id,
            'session_id'     => $session->id,
            'status'         => 1,
            'note'           => '',
            'attendanceType' => 1,
        ]];

        $this->service->saveBulkAttendance($draft);
        $this->service->saveBulkAttendance($draft);

        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $student->id,
            'session_id' => $session->id,
        ]);

        $this->assertEquals(
            1,
            AttendanceRecord::where('student_id', $student->id)
                ->where('session_id', $session->id)
                ->count()
        );
    }

    public function test_session_da_khoa_khong_luu_duoc(): void
    {
        $user    = User::first();
        $session = AttendanceSession::factory()->closed()->create();
        $student = StudentNew::first();

        $this->actingAs($user);

        $result = $this->service->saveBulkAttendance([[
            'student_id'     => $student->id,
            'session_id'     => $session->id,
            'status'         => 1,
            'note'           => '',
            'attendanceType' => 1,
        ]]);

        $this->assertFalse($result['success']);
        $this->assertDatabaseMissing('attendance_records', [
            'student_id' => $student->id,
            'session_id' => $session->id,
        ]);
    }
}
