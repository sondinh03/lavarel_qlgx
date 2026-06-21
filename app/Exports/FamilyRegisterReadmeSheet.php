<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class FamilyRegisterReadmeSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            ['HƯỚNG DẪN IMPORT SỔ GIA ĐÌNH CÔNG GIÁO'],
            [''],
            ['1. File gồm 3 sheet dữ liệu: parishioners, sacraments, marriages'],
            ['2. Dòng 5 là tên cột kỹ thuật — KHÔNG sửa tên cột'],
            ['3. Dòng 6 trở đi là dữ liệu'],
            [''],
            ['temp_id: mã tạm do bạn tự đặt (P001, P002...) để liên kết giữa các sheet'],
            ['family_temp_id: mã gia đình tạm (F001) — các thành viên cùng gia đình dùng chung mã'],
            ['family_role: husband | wife | child | other'],
            ['father_temp_id / mother_temp_id: temp_id của cha/mẹ (nếu có trong cùng file)'],
            ['parishioner_temp_id: temp_id người lãnh bí tích (sheet sacraments)'],
            ['husband_temp_id / wife_temp_id: temp_id vợ chồng (sheet marriages)'],
            [''],
            ['gender: male / female (hoặc nam / nữ)'],
            ['type (bí tích): baptism | communion | confirmation | anointing | holy_orders'],
            ['status (hôn phối): valid | invalid | widowed | divorced'],
            [''],
            ['Ngày tháng: dd/mm/yyyy'],
            ['saint_name: tên thánh — sẽ tự tạo nếu chưa có trong hệ thống'],
            ['parish_name: tên giáo xứ — nếu không khớp sẽ dùng giáo xứ đang import'],
            [''],
            ['Lưu ý: Mỗi family_temp_id chỉ có tối đa 1 husband và 1 wife'],
            ['OCR từ ảnh scan (nếu có) cần kiểm tra thủ công trước khi import'],
        ];
    }

    public function title(): string
    {
        return 'README';
    }
}
