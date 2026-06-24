<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class FamilyRegisterHouseholdsSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['SỔ GIA ĐÌNH — HỘ GIA ĐÌNH'],
            ['Mỗi dòng = 1 hộ. ma_ho dùng để liên kết với sheet thanh_vien'],
            [
                'Mã hộ', 'Mã GD', 'Tên hộ', 'Giáo họ', 'Địa chỉ', 'Tỉnh/TP', 'Xã/Phường',
                'Điện thoại', 'Ghi chú',
            ],
            [
                '(bắt buộc)', '(tùy chọn — trống sẽ tự sinh)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
            ],
            [
                'ma_ho', 'ma_gd', 'ten_ho', 'gio_ho', 'dia_chi', 'tinh', 'xa',
                'dien_thoai', 'ghi_chu',
            ],
            [
                'H001', '', 'GĐ Hoàng Công Luật', 'Giáo họ 1', '123 Đường ABC', 'TP. Hồ Chí Minh', 'Phường 1',
                '0901234567', '',
            ],
        ];
    }

    public function title(): string
    {
        return 'ho_gia_dinh';
    }
}
