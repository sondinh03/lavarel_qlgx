<?php

namespace App\Exports;

use App\Models\TeacherAttendanceSession;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Xuất tổng kết điểm danh GLV — mỗi tab một loại buổi.
 */
class TeacherAttendanceWorkbookExport implements WithMultipleSheets
{
    public function __construct(
        private int $parishId,
        private int $namHocId,
        private ?int $attendanceType = null,
    ) {}

    public function sheets(): array
    {
        $types = $this->attendanceType !== null
            ? [$this->attendanceType]
            : [
                TeacherAttendanceSession::TYPE_TEACH,
                TeacherAttendanceSession::TYPE_CEREMONY,
                TeacherAttendanceSession::TYPE_MEETING,
            ];

        $sheets = [];
        foreach ($types as $type) {
            $label = TeacherAttendanceSession::typeLabel((int) $type);
            $sheets[] = new TeacherAttendanceExport(
                $this->parishId,
                $this->namHocId,
                (int) $type,
                $label,
            );
        }

        return $sheets;
    }
}
