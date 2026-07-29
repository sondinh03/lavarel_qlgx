<?php

namespace App\Exports;

use App\Models\AttendanceSession;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Xuất tổng kết điểm danh một lớp — mỗi tab một loại buổi.
 *
 * @param  int|null  $attendanceType  1=đi học, 2=đi lễ, null=cả hai tab
 */
class AttendanceWorkbookExport implements WithMultipleSheets
{
    public function __construct(
        private int $classId,
        private ?int $attendanceType = null,
    ) {}

    public function sheets(): array
    {
        if ($this->attendanceType === AttendanceSession::TYPE_CLASS) {
            return [
                new AttendanceExport($this->classId, null, AttendanceSession::TYPE_CLASS, 'Đi học'),
            ];
        }

        if ($this->attendanceType === AttendanceSession::TYPE_CEREMONY) {
            return [
                new AttendanceExport($this->classId, null, AttendanceSession::TYPE_CEREMONY, 'Đi lễ'),
            ];
        }

        return [
            new AttendanceExport($this->classId, null, AttendanceSession::TYPE_CLASS, 'Đi học'),
            new AttendanceExport($this->classId, null, AttendanceSession::TYPE_CEREMONY, 'Đi lễ'),
        ];
    }
}
