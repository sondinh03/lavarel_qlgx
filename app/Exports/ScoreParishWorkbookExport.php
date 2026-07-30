<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Bảng điểm toàn giáo xứ trong một năm học — mỗi lớp là một sheet.
 */
class ScoreParishWorkbookExport implements WithMultipleSheets
{
    /**
     * @param  Collection<int, object{id: int, name: string}>  $classes
     */
    public function __construct(private Collection $classes) {}

    public function sheets(): array
    {
        $usedTitles = [];

        return $this->classes
            ->map(function ($class) use (&$usedTitles) {
                $title = $this->uniqueSheetTitle((string) $class->name, (int) $class->id, $usedTitles);
                $usedTitles[] = mb_strtolower($title);

                return new ScoreExport((int) $class->id, null, $title);
            })
            ->all();
    }

    /**
     * Excel giới hạn tên sheet 31 ký tự, cấm một số ký tự và không cho trùng tên.
     *
     * @param  string[]  $usedTitles
     */
    private function uniqueSheetTitle(string $className, int $classId, array $usedTitles): string
    {
        $base = trim((string) preg_replace('/[\\\\\/\?\*\[\]\:]/u', '-', $className));
        $base = $base !== '' ? $base : 'Lớp ' . $classId;
        $title = mb_substr($base, 0, 31);

        if (! in_array(mb_strtolower($title), $usedTitles, true)) {
            return $title;
        }

        $suffix = '-' . $classId;

        return mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
    }
}
