<?php

namespace App\Exports;

use App\Models\CatechismClass;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Xuất tổng kết điểm danh toàn giáo xứ — mỗi lớp một sheet (cả năm).
 *
 * @param  int|null  $attendanceType  1=đi học, 2=đi lễ, null=cả hai
 */
class AttendanceParishWorkbookExport implements WithMultipleSheets
{
    public function __construct(
        private int $parishId,
        private int $namHocId,
        private ?int $attendanceType = null,
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

            $sheets[] = new AttendanceExport(
                (int) $class->id,
                null,
                $this->attendanceType,
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
