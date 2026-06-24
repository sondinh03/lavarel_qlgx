<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class FamilyRegisterMembersSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['SỔ GIA ĐÌNH — THÀNH VIÊN'],
            ['Mỗi dòng = 1 người. Bí tích điền cột ngang; hôn phối suy ra từ chồng+vợ hoặc cột hp_*'],
            [
                'Mã TV', 'Mã hộ', 'Vai trò', 'Họ', 'Tên', 'Tên thánh', 'Giới tính',
                'Ngày sinh', 'Nơi sinh', 'Con thứ', 'Mã cha', 'Mã mẹ', 'Ghi chú',
                'RT ngày', 'RT số', 'RT quyển', 'RT nơi', 'RT ban', 'RT đỡ đầu',
                'TT ngày', 'TT số', 'TS ngày', 'TS số', 'TS ban',
                'HP ngày', 'HP số', 'HP GX', 'HP NC1', 'HP NC2', 'HP LM', 'HP TT',
            ],
            [
                '(bắt buộc)', '(bắt buộc)', 'husband/wife/child/other', '(bắt buộc)', '(bắt buộc)',
                '(tùy chọn)', 'male/female', 'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)',
                '(ma_tv cha/mẹ)', '(ma_tv cha/mẹ)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', 'valid/...',
            ],
            [
                'ma_tv', 'ma_ho', 'vai_tro', 'ho', 'ten', 'ten_thanh', 'gioi_tinh',
                'ngay_sinh', 'noi_sinh', 'con_thu', 'ma_cha', 'ma_me', 'ghi_chu',
                'rt_ngay', 'rt_so', 'rt_quyen', 'rt_noi', 'rt_ban', 'rt_do_dau',
                'tt_ngay', 'tt_so', 'ts_ngay', 'ts_so', 'ts_ban',
                'hp_ngay', 'hp_so', 'hp_gx', 'hp_nc1', 'hp_nc2', 'hp_lm', 'hp_trang_thai',
            ],
            [
                'P001', 'H001', 'husband', 'Hoàng Công', 'Luật', 'Giuse', 'male',
                '15/03/1965', 'Hà Nội', '1', '', '', '',
                '20/03/1965', '101', '1', 'GX Tân Định', 'LM Phêrô', 'Maria',
                '', '', '15/05/1980', '201', 'GM Phêrô',
                '10/06/1990', 'HP-001', 'GX Tân Định', 'Nguyễn Văn A', 'Trần Thị B', 'LM Phêrô', 'valid',
            ],
            [
                'P002', 'H001', 'wife', 'Nguyễn Thị', 'Hoa', 'Maria', 'female',
                '20/08/1968', 'Hà Nội', '2', '', '', '',
                '25/08/1968', '102', '1', 'GX Tân Định', 'LM Phêrô', 'Anna',
                '20/05/1980', '202', '', '', '',
                '', '', '', '', '', '', '',
            ],
            [
                'P003', 'H001', 'child', 'Hoàng Công', 'Minh', 'Giuse', 'male',
                '10/05/1995', 'TP. Hồ Chí Minh', '1', 'P001', 'P002', '',
                '15/05/1995', '301', '3', 'GX Tân Định', 'LM Phêrô', 'Maria',
                '20/05/2003', '302', '', '', '',
                '', '', '', '', '', '', '',
            ],
            [
                'P004', 'H001', 'child', 'Hoàng Công', 'Lan', 'Maria', 'female',
                '22/11/1998', 'TP. Hồ Chí Minh', '2', 'P001', 'P002', '',
                '01/12/1998', '401', '4', 'GX Tân Định', 'LM Phêrô', 'Giuse',
                '', '', '', '', '',
                '', '', '', '', '', '', '',
            ],
        ];
    }

    public function title(): string
    {
        return 'thanh_vien';
    }
}
