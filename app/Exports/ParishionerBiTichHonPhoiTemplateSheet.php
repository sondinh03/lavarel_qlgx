<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ParishionerBiTichHonPhoiTemplateSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['XỨC DẦU & HÔN PHỐI'],
            ['IMPORT XỨC DẦU VÀ HÔN PHỐI — KHỚP THEO HỌ TÊN + NGÀY SINH (TÙY CHỌN)'],
            [
                'Họ tên đệm', 'Tên', 'Ngày sinh',
                'Xức dầu — ngày', 'Xức dầu — tình trạng', 'Xức dầu — người ban',
                'Hôn phối — ngày', 'Hôn phối — số', 'Hôn phối — nơi', 'Hôn phối — tỉnh',
                'Hôn phối — LM chứng', 'Hôn phối — nhân chứng 1', 'Hôn phối — nhân chứng 2', 'Hôn phối — tình trạng',
            ],
            [
                '(bắt buộc)', '(bắt buộc)', 'dd/mm/yyyy',
                'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)',
                'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', 'hợp lệ / bất hợp lệ / góa / ly hôn',
            ],
            [
                'ho_ten_dem', 'ten', 'ngay_sinh',
                'xuc_dau_ngay', 'xuc_dau_tinh_trang', 'xuc_dau_nguoi_ban',
                'hon_phoi_ngay', 'hon_phoi_so', 'hon_phoi_noi', 'hon_phoi_tinh',
                'hon_phoi_lm_chung', 'hon_phoi_nhan_chung_1', 'hon_phoi_nhan_chung_2', 'hon_phoi_tinh_trang',
            ],
            [
                'Nguyễn Văn', 'An', '15/03/1990',
                '', '', '',
                '', '', '', '',
                '', '', '', '',
            ],
        ];
    }

    public function title(): string
    {
        return 'BiTichHonPhoi';
    }
}
