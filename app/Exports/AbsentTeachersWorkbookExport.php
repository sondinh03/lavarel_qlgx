<?php

namespace App\Exports;

use App\Models\TeacherAttendanceRecord;
use App\Models\TeacherAttendanceSession;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Xuất GLV vắng — mỗi loại buổi một sheet (hoặc một sheet nếu đã lọc loại).
 */
class AbsentTeachersWorkbookExport implements WithMultipleSheets
{
    /**
     * @param  list<int>  $statuses
     */
    public function __construct(
        private int $parishId,
        private int $namHocId,
        private string $fromDate,
        private string $toDate,
        private ?int $attendanceType = null,
        private array $statuses = [
            TeacherAttendanceRecord::STATUS_ABSENT_EXCUSED,
            TeacherAttendanceRecord::STATUS_ABSENT_UNEXCUSED,
        ],
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
            $sheets[] = new AbsentTeachersExport(
                $this->parishId,
                $this->namHocId,
                $this->fromDate,
                $this->toDate,
                (int) $type,
                $this->statuses,
                $label,
            );
        }

        return $sheets;
    }
}
