<?php

namespace App\Support;

final class CatechistPermissions
{
    public const MANAGE_PARISH_SCORES = 'manage_parish_scores';

    public const EDIT_PARISH_STUDENTS = 'edit_parish_students';

    public const MARK_TEACHER_ATTENDANCE = 'mark_teacher_attendance';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::MANAGE_PARISH_SCORES,
            self::EDIT_PARISH_STUDENTS,
            self::MARK_TEACHER_ATTENDANCE,
        ];
    }

    public static function labels(): array
    {
        return [
            self::MANAGE_PARISH_SCORES => 'Quản lý điểm toàn giáo xứ',
            self::EDIT_PARISH_STUDENTS => 'Sửa thông tin học sinh toàn giáo xứ',
            self::MARK_TEACHER_ATTENDANCE => 'Điểm danh giáo lý viên',
        ];
    }
}
