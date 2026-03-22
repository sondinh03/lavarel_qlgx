<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

/**
 * Chỉ dùng để đọc/parse file Excel thành array.
 * Không write DB — việc đó để ImportTeacherAction lo.
 *
 * Cấu trúc file:
 *   Dòng 1: Tên giáo xứ
 *   Dòng 2: Tiêu đề bảng
 *   Dòng 3: Label tiếng Việt  (bỏ qua)
 *   Dòng 4: Hướng dẫn         (bỏ qua)
 *   Dòng 5: Tên cột kỹ thuật  ← WithHeadingRow đọc từ đây
 *   Dòng 6+: Dữ liệu
 */
class TeacherPreviewImport implements ToCollection, WithHeadingRow
{
    public function headingRow(): int
    {
        return 5;
    }

    public function collection(Collection $rows): Collection
    {
        return $rows;
    }
}