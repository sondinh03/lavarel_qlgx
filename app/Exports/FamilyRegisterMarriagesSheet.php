<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class FamilyRegisterMarriagesSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['SỔ GIA ĐÌNH — HÔN PHỐI'],
            ['Mỗi dòng = 1 hôn phối. husband_temp_id và wife_temp_id phải có trong sheet parishioners'],
            [
                'Chồng (temp_id)', 'Vợ (temp_id)', 'Ngày HP', 'Số HP', 'Giáo xứ',
                'Nhân chứng 1', 'Nhân chứng 2', 'LM chứng', 'Tình trạng', 'Ghi chú',
            ],
            [
                '(bắt buộc)', '(bắt buộc)', 'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', 'valid/invalid/widowed/divorced', '(tùy chọn)',
            ],
            [
                'husband_temp_id', 'wife_temp_id', 'married_date', 'certificate_number', 'parish_name',
                'witness_1', 'witness_2', 'priest_witness', 'status', 'note',
            ],
            [
                'P001', 'P002', '10/06/1990', 'HP-001', 'GX Tân Định',
                'Nguyễn Văn A', 'Trần Thị B', 'LM Phêrô', 'valid', '',
            ],
        ];
    }

    public function title(): string
    {
        return 'marriages';
    }
}
