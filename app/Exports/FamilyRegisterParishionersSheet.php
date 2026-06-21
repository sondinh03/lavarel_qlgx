<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class FamilyRegisterParishionersSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['SỔ GIA ĐÌNH — GIÁO DÂN'],
            ['Mỗi dòng = 1 người. Dùng temp_id để liên kết với sheet sacraments và marriages'],
            [
                'Mã tạm', 'Mã GD tạm', 'Vai trò', 'Họ tên đệm', 'Tên', 'Giới tính',
                'Ngày sinh', 'Nơi sinh', 'Con thứ', 'Tên thánh',
                'Cha (temp_id)', 'Mẹ (temp_id)', 'Giáo xứ', 'Ghi chú',
            ],
            [
                '(bắt buộc)', '(bắt buộc)', 'husband/wife/child/other', '(bắt buộc)', '(bắt buộc)',
                'male/female', 'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
            ],
            [
                'temp_id', 'family_temp_id', 'family_role', 'last_name', 'first_name', 'gender',
                'birthday', 'birth_place', 'birth_order', 'saint_name',
                'father_temp_id', 'mother_temp_id', 'parish_name', 'note',
            ],
            [
                'P001', 'F001', 'husband', 'Hoàng Công', 'Luật', 'male',
                '15/03/1965', 'Hà Nội', '1', 'Giuse',
                '', '', 'GX Tân Định', '',
            ],
            [
                'P002', 'F001', 'wife', 'Nguyễn Thị', 'Hoa', 'female',
                '20/08/1968', 'Hà Nội', '2', 'Maria',
                '', '', 'GX Tân Định', '',
            ],
            [
                'P003', 'F001', 'child', 'Hoàng Công', 'Minh', 'male',
                '10/05/1995', 'TP. Hồ Chí Minh', '1', 'Giuse',
                'P001', 'P002', 'GX Tân Định', '',
            ],
            [
                'P004', 'F001', 'child', 'Hoàng Công', 'Lan', 'female',
                '22/11/1998', 'TP. Hồ Chí Minh', '2', 'Maria',
                'P001', 'P002', 'GX Tân Định', '',
            ],
        ];
    }

    public function title(): string
    {
        return 'parishioners';
    }
}
