<?php

namespace App\Support;

final class CatechistPermissions
{
    public const MANAGE_PARISH_SCORES = 'manage_parish_scores';

    public const EDIT_PARISH_STUDENTS = 'edit_parish_students';

    public const MARK_TEACHER_ATTENDANCE = 'mark_teacher_attendance';

    public const CREATE_ATTENDANCE_SESSIONS = 'create_attendance_sessions';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::MANAGE_PARISH_SCORES,
            self::EDIT_PARISH_STUDENTS,
            self::MARK_TEACHER_ATTENDANCE,
            self::CREATE_ATTENDANCE_SESSIONS,
        ];
    }

    public static function labels(): array
    {
        return [
            self::MANAGE_PARISH_SCORES => 'Quản lý điểm toàn giáo xứ',
            self::EDIT_PARISH_STUDENTS => 'Sửa thông tin học sinh toàn giáo xứ',
            self::MARK_TEACHER_ATTENDANCE => 'Điểm danh giáo lý viên',
            self::CREATE_ATTENDANCE_SESSIONS => 'Tạo phiên điểm danh',
        ];
    }
}
