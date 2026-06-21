<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ParishionerGiaoDanTemplateSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['DANH SÁCH GIÁO DÂN'],
            ['IMPORT GIÁO DÂN TỪ EXCEL — SHEET GIÁO DÂN'],
            [
                'Tên thánh', 'Họ tên đệm', 'Tên', 'Ngày sinh', 'Giới tính', 'Giáo họ',
                'Số điện thoại', 'Email', 'CCCD', 'Họ tên bố', 'Họ tên mẹ',
                'Tình trạng hôn nhân', 'Tân tòng', 'Ghi chú', 'Quê quán',
                'Địa chỉ thường trú', 'Tỉnh/TP thường trú', 'Con thứ', 'Dân tộc',
                'Nghề nghiệp', 'Trình độ học vấn', 'Trình độ chuyên môn', 'Trình độ giáo lý',
                'Chức vụ', 'Cấp bậc', 'Xã/phường TT', 'Địa chỉ tạm trú', 'Tỉnh/TP tạm trú',
                'Ngày gia nhập xứ', 'Ngày mất', 'Số sổ mất', 'Nơi an táng',
                'Rửa tội — ngày', 'Rửa tội — số', 'Rửa tội — người ban', 'Rửa tội — đỡ đầu', 'Rửa tội — giáo xứ',
                'Rước lễ — ngày', 'Rước lễ — số', 'Rước lễ — người ban', 'Rước lễ — giáo xứ',
                'Thêm sức — ngày', 'Thêm sức — số', 'Thêm sức — người ban', 'Thêm sức — đỡ đầu', 'Thêm sức — giáo xứ',
            ],
            [
                '(tùy chọn)', '(bắt buộc)', '(bắt buộc)', 'dd/mm/yyyy', 'nam / nữ', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                'độc thân / đã kết hôn / góa / ly hôn', 'có / không', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                'tên hoặc mã xã', '(tùy chọn)', '(tùy chọn)', 'dd/mm/yyyy', 'dd/mm/yyyy',
                '(tùy chọn)', '(tùy chọn)',
                'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
                'dd/mm/yyyy', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)', '(tùy chọn)',
            ],
            [
                'ten_thanh', 'ho_ten_dem', 'ten', 'ngay_sinh', 'gioi_tinh', 'giao_ho',
                'so_dien_thoai', 'email', 'cccd', 'ho_ten_bo', 'ho_ten_me',
                'tinh_trang_hon_nhan', 'tan_tong', 'ghi_chu', 'que_quan',
                'dia_chi_thuong_tru', 'tinh_thuong_tru', 'con_thu', 'dan_toc',
                'nghe_nghiep', 'trinh_do_hoc_van', 'trinh_do_chuyen_mon', 'trinh_do_giao_ly',
                'chuc_vu', 'cap_bac', 'xa_thuong_tru', 'dia_chi_tam_tru', 'tinh_tam_tru',
                'ngay_gia_nhap', 'ngay_mat', 'so_so_mat', 'noi_an_tang',
                'rua_toi_ngay', 'rua_toi_so', 'rua_toi_nguoi_ban', 'rua_toi_dau_dau', 'rua_toi_giao_xu',
                'ruoc_le_ngay', 'ruoc_le_so', 'ruoc_le_nguoi_ban', 'ruoc_le_giao_xu',
                'them_suc_ngay', 'them_suc_so', 'them_suc_nguoi_ban', 'them_suc_dau_dau', 'them_suc_giao_xu',
            ],
            [
                'Giuse', 'Nguyễn Văn', 'An', '15/03/1990', 'nam', 'Giáo họ Thánh Giuse',
                '0901234567', 'an@example.com', '', 'Nguyễn Văn B', 'Trần Thị M',
                'độc thân', 'không', '', 'Hà Nội', '123 đường ABC', 'TP. Hồ Chí Minh',
                '2', 'Kinh', 'Giáo viên', 'Đại học', 'Đại học', 'Thêm sức',
                'Giáo dân thường', 'Cấp I', 'Phường 1', '', '',
                '01/01/2020', '', '', '',
                '20/03/1990', '123', 'LM Phêrô', 'Maria', 'GX Tân Định',
                '20/05/2003', '789', 'LM Phêrô', 'GX Tân Định',
                '15/05/2005', '456', 'LM Phêrô', 'Giuse', 'GX Tân Định',
            ],
        ];
    }

    public function title(): string
    {
        return 'GiaoDan';
    }
}
