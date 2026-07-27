<?php

namespace Tests\Unit;

use App\Support\CatechistPermissions;
use PHPUnit\Framework\TestCase;

class CatechistPermissionsTest extends TestCase
{
    public function test_permission_names_are_stable(): void
    {
        $this->assertSame('manage_parish_scores', CatechistPermissions::MANAGE_PARISH_SCORES);
        $this->assertSame('edit_parish_students', CatechistPermissions::EDIT_PARISH_STUDENTS);
        $this->assertSame('mark_teacher_attendance', CatechistPermissions::MARK_TEACHER_ATTENDANCE);
        $this->assertSame('create_attendance_sessions', CatechistPermissions::CREATE_ATTENDANCE_SESSIONS);
        $this->assertSame(
            [
                CatechistPermissions::MANAGE_PARISH_SCORES,
                CatechistPermissions::EDIT_PARISH_STUDENTS,
                CatechistPermissions::MARK_TEACHER_ATTENDANCE,
                CatechistPermissions::CREATE_ATTENDANCE_SESSIONS,
            ],
            CatechistPermissions::all()
        );
    }
}
