<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\CatechismClass;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Xuất học sinh vắng toàn giáo xứ — mỗi lớp một sheet.
 */
class AbsentStudentsWorkbookExport implements WithMultipleSheets
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
            AttendanceRecord::STATUS_ABSENT_EXCUSED,
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
        ],
    ) {}

    public function sheets(): array
    {
        $classes = CatechismClass::query()
            ->where('classes.parish_id', $this->parishId)
            ->where('classes.school_year_id', $this->namHocId)
            ->active()
            ->ordered()
            ->select('classes.id', 'classes.name')
            ->get();

        $usedTitles = [];
        $sheets = [];

        foreach ($classes as $class) {
            $base = $this->sanitizeSheetTitle((string) $class->name);
            $title = $base;
            $n = 2;
            while (isset($usedTitles[$title])) {
                $suffix = " ({$n})";
                $title = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
                $n++;
            }
            $usedTitles[$title] = true;

            $sheets[] = new AbsentStudentsExport(
                (int) $class->id,
                $this->fromDate,
                $this->toDate,
                $this->attendanceType,
                $this->statuses,
                $title,
            );
        }

        return $sheets;
    }

    private function sanitizeSheetTitle(string $name): string
    {
        $clean = preg_replace('/[\\\\\/\?\*\[\]\:]/', '-', $name) ?: 'Lop';
        $clean = trim($clean);

        return mb_substr($clean !== '' ? $clean : 'Lop', 0, 31);
    }
}
