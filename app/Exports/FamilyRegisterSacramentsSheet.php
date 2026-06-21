<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class FamilyRegisterSacramentsSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['SỔ GIA ĐÌNH — BÍ TÍCH'],
            ['Mỗi dòng = 1 bí tích. parishioner_temp_id phải khớp temp_id trong sheet parishioners'],
            [
                'Mã GD tạm', 'Loại bí tích', 'Ngày lãnh', 'Số chứng thư', 'Số quyển',
                'Người ban', 'Đỡ đầu', 'Giáo xứ', 'Ghi chú',
            ],
            [
                '(bắt buộc)', 'baptism/communion/confirmation/anointing/holy_orders',
                'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
            ],
            [
                'parishioner_temp_id', 'type', 'received_date', 'certificate_number', 'book_number',
                'giver', 'sponsor', 'parish_name', 'note',
            ],
            [
                'P001', 'baptism', '20/03/1965', '101', '1', 'LM Phêrô', 'Maria', 'GX Tân Định', '',
            ],
            [
                'P001', 'confirmation', '15/05/1980', '201', '2', 'GM Phêrô', 'Giuse', 'GX Tân Định', '',
            ],
            [
                'P002', 'baptism', '25/08/1968', '102', '1', 'LM Phêrô', 'Anna', 'GX Tân Định', '',
            ],
            [
                'P003', 'baptism', '15/05/1995', '301', '3', 'LM Phêrô', 'Maria', 'GX Tân Định', '',
            ],
            [
                'P003', 'communion', '20/05/2003', '302', '3', 'LM Phêrô', '', 'GX Tân Định', '',
            ],
            [
                'P004', 'baptism', '01/12/1998', '401', '4', 'LM Phêrô', 'Giuse', 'GX Tân Định', '',
            ],
        ];
    }

    public function title(): string
    {
        return 'sacraments';
    }
}
