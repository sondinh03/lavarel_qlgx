<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;

/**
 * Chỉ dùng để đọc/parse file Excel thành array.
 * Không write DB — việc đó để ImportStudentAction lo.
 *
 * Cấu trúc file:
 *   Dòng 1: Tên giáo xứ
 *   Dòng 2: Năm học
 *   Dòng 3: Tiêu đề bảng
 *   Dòng 4: Label tiếng Việt (bỏ qua)
 *   Dòng 5: Tên cột kỹ thuật  ← WithHeadingRow đọc từ đây
 *   Dòng 6+: Dữ liệu
 */
class StudentPreviewImport implements ToCollection, WithHeadingRow
{
    /**
     * Bắt đầu đọc từ dòng 5 (dòng tên cột kỹ thuật).
     * WithHeadingRow sẽ dùng dòng này làm key.
     */
    // public function startRow(): int
    // {
    //     return 5;
    // }

    public function headingRow(): int
    {
        return 5;
    }

    public function collection(Collection $rows): Collection
    {
        return $rows;
    }
}
