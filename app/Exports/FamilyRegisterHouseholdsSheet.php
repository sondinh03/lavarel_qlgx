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
            ['Mỗi dòng = 1 hộ. Thông tin hôn phối điền trên dòng hộ (cặp chồng–vợ suy ra từ sheet thành viên).'],
            [
                'Mã hộ gia đình', 'Mã gia đình', 'Tên hộ gia đình', 'Giáo họ', 'Địa chỉ', 'Tỉnh/Thành', 'Xã/Phường',
                'Điện thoại', 'Ghi chú',
                'Hôn phối ngày', 'Hôn phối số', 'Hôn phối giáo xứ', 'Hôn phối nhân chứng 1', 'Hôn phối nhân chứng 2',
                'Hôn phối linh mục', 'Hôn phối trạng thái', 'Hôn phối ghi chú',
            ],
            [
                '(bắt buộc)', '(tùy chọn — trống sẽ tự sinh)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', 'Hợp lệ / Góa / Ly hôn', '(tùy chọn)',
            ],
            [
                'ma_ho_gia_dinh', 'ma_gia_dinh', 'ten_ho_gia_dinh', 'giao_ho', 'dia_chi', 'tinh_thanh', 'xa_phuong',
                'dien_thoai', 'ghi_chu',
                'hon_phoi_ngay', 'hon_phoi_so', 'hon_phoi_giao_xu', 'hon_phoi_nhan_chung_1', 'hon_phoi_nhan_chung_2',
                'hon_phoi_linh_muc', 'hon_phoi_trang_thai', 'hon_phoi_ghi_chu',
            ],
            [
                'H001', '', 'GĐ Hoàng Công Luật', 'Giáo họ 1', '123 Đường ABC', 'TP. Hồ Chí Minh', 'Phường 1',
                '0901234567', '',
                '10/06/1990', 'HP-001', 'GX Tân Định', 'Nguyễn Văn A', 'Trần Thị B', 'LM Phêrô', 'Hợp lệ', '',
            ],
        ];
    }

    public function title(): string
    {
        return 'ho_gia_dinh';
    }
}
