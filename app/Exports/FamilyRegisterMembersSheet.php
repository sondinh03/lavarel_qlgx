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
            ['Mỗi dòng = 1 người. Bí tích điền cột ngang. Hôn phối điền trên sheet hộ gia đình.'],
            [
                'Mã thành viên', 'Mã hộ gia đình', 'Vai trò', 'Họ', 'Tên', 'Tên thánh', 'Giới tính',
                'Ngày sinh', 'Nơi sinh', 'Con thứ', 'Mã cha', 'Mã mẹ', 'Ghi chú', 'Giáo xứ', 'Hội đoàn',
                'Rửa tội ngày', 'Rửa tội số', 'Rửa tội quyển', 'Rửa tội nơi', 'Rửa tội ban', 'Rửa tội đỡ đầu', 'Rửa tội ghi chú',
                'Rước lễ ngày', 'Rước lễ số', 'Rước lễ quyển', 'Rước lễ nơi', 'Rước lễ ban', 'Rước lễ đỡ đầu', 'Rước lễ ghi chú',
                'Thêm sức ngày', 'Thêm sức số', 'Thêm sức quyển', 'Thêm sức nơi', 'Thêm sức ban', 'Thêm sức đỡ đầu', 'Thêm sức ghi chú',
                'Xức dấu ngày', 'Xức dấu số', 'Xức dấu quyển', 'Xức dấu nơi', 'Xức dấu ban', 'Xức dấu đỡ đầu', 'Xức dấu ghi chú',
                'Truyền chức ngày', 'Truyền chức số', 'Truyền chức quyển', 'Truyền chức nơi', 'Truyền chức ban', 'Truyền chức đỡ đầu', 'Truyền chức ghi chú',
            ],
            [
                '(bắt buộc)', '(bắt buộc)', 'Chồng / Vợ / Con / Khác', '(bắt buộc)', '(bắt buộc)',
                '(tùy chọn)', 'Nam / Nữ', 'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)',
                '(mã thành viên cha/mẹ)', '(mã thành viên cha/mẹ)', '(tùy chọn)', '(tùy chọn)', '(tên hội đoàn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
            ],
            [
                'ma_thanh_vien', 'ma_ho_gia_dinh', 'vai_tro', 'ho', 'ten', 'ten_thanh', 'gioi_tinh',
                'ngay_sinh', 'noi_sinh', 'con_thu', 'ma_cha', 'ma_me', 'ghi_chu', 'gio_xu', 'hoi_doan',
                'rua_toi_ngay', 'rua_toi_so', 'rua_toi_quyen', 'rua_toi_noi', 'rua_toi_ban', 'rua_toi_do_dau', 'rua_toi_ghi_chu',
                'ruoc_le_ngay', 'ruoc_le_so', 'ruoc_le_quyen', 'ruoc_le_noi', 'ruoc_le_ban', 'ruoc_le_do_dau', 'ruoc_le_ghi_chu',
                'them_suc_ngay', 'them_suc_so', 'them_suc_quyen', 'them_suc_noi', 'them_suc_ban', 'them_suc_do_dau', 'them_suc_ghi_chu',
                'xung_toi_ngay', 'xung_toi_so', 'xung_toi_quyen', 'xung_toi_noi', 'xung_toi_ban', 'xung_toi_do_dau', 'xung_toi_ghi_chu',
                'truyen_chuc_ngay', 'truyen_chuc_so', 'truyen_chuc_quyen', 'truyen_chuc_noi', 'truyen_chuc_ban', 'truyen_chuc_do_dau', 'truyen_chuc_ghi_chu',
            ],
            [
                'P001', 'H001', 'Chồng', 'Hoàng Công', 'Luật', 'Giuse', 'Nam',
                '15/03/1965', 'Hà Nội', '1', '', '', '', '', 'Hội đoàn Thánh Giuse',
                '20/03/1965', '101', '1', 'GX Tân Định', 'LM Phêrô', 'Maria', '',
                '', '', '', '', '', '', '',
                '15/05/1980', '201', '2', 'GX Tân Định', 'GM Phêrô', '', '',
                '', '', '', '', '', '', '',
                '', '', '', '', '', '', '',
            ],
            [
                'P002', 'H001', 'Vợ', 'Nguyễn Thị', 'Hoa', 'Maria', 'Nữ',
                '20/08/1968', 'Hà Nội', '2', '', '', '', '', 'Hội đoàn Thánh Giuse',
                '25/08/1968', '102', '1', 'GX Tân Định', 'LM Phêrô', 'Anna', '',
                '20/05/1980', '202', '2', 'GX Tân Định', 'LM Phêrô', 'Giuse', '',
                '', '', '', '', '', '', '',
                '', '', '', '', '', '', '',
                '', '', '', '', '', '', '',
            ],
            [
                'P003', 'H001', 'Con', 'Hoàng Công', 'Minh', 'Giuse', 'Nam',
                '10/05/1995', 'TP. Hồ Chí Minh', '1', 'P001', 'P002', '', '', '',
                '15/05/1995', '301', '3', 'GX Tân Định', 'LM Phêrô', 'Maria', '',
                '20/05/2003', '302', '3', 'GX Tân Định', 'LM Phêrô', '', '',
                '', '', '', '', '', '', '',
                '', '', '', '', '', '', '',
                '', '', '', '', '', '', '',
            ],
            [
                'P004', 'H001', 'Con', 'Hoàng Công', 'Lan', 'Maria', 'Nữ',
                '22/11/1998', 'TP. Hồ Chí Minh', '2', 'P001', 'P002', '', '', '',
                '01/12/1998', '401', '4', 'GX Tân Định', 'LM Phêrô', 'Giuse', '',
                '', '', '', '', '', '', '',
                '', '', '', '', '', '', '',
                '', '', '', '', '', '', '',
                '', '', '', '', '', '', '',
            ],
        ];
    }

    public function title(): string
    {
        return 'thanh_vien';
    }
}
