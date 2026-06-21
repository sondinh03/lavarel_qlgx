<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Đọc file Excel import giáo dân thành array.
 *
 * Cấu trúc file:
 *   Dòng 1: Tiêu đề
 *   Dòng 2: Mô tả
 *   Dòng 3: Nhãn tiếng Việt (bỏ qua)
 *   Dòng 4: Hướng dẫn (bỏ qua)
 *   Dòng 5: Tên cột kỹ thuật ← WithHeadingRow đọc từ đây
 *   Dòng 6+: Dữ liệu
 */
class ParishionerPreviewImport implements ToCollection, WithHeadingRow
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
