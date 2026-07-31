<?php

namespace App\Support;

use App\Models\Teacher;

class TeacherImportDuplicateMessage
{
    /**
     * Khoá nhận diện trùng hồ sơ: tên thánh + họ tên + ngày sinh.
     */
    public static function duplicateKey(?int $saintId, string $lastName, string $firstName, ?string $birthdayYmd): string
    {
        $fullName = mb_strtolower(trim($lastName . ' ' . $firstName), 'UTF-8');

        return ($saintId ?? '')
            . '_' . $fullName
            . '_' . ($birthdayYmd ?? '');
    }

    public static function forProfileMatch(Teacher $teacher): string
    {
        $label    = e($teacher->full_name_with_saint);
        $code     = e($teacher->teacher_code ?? '—');
        $birthday = $teacher->birthday?->format('d/m/Y');

        $parts = [
            "Đã có hồ sơ trong giáo xứ: <strong>{$label}</strong> (mã <strong>{$code}</strong>"
                . ($birthday ? ", sinh {$birthday}" : '') . ').',
        ];

        if (! $teacher->is_active) {
            $parts[] = 'Hồ sơ này đang ở trạng thái <strong>đã nghỉ</strong>.'
                . ' → Nếu người này quay lại, hãy mở trang <strong>Giáo lý viên</strong> và bật lại hoạt động'
                . ", hoặc điền mã <strong>{$code}</strong> vào cột «Mã GLV» để cập nhật.";
        } else {
            $parts[] = "→ Điền mã <strong>{$code}</strong> vào cột «Mã GLV» rồi import lại để"
                . ' <strong>cập nhật</strong> thông tin (SĐT, giáo họ, email…).';
        }

        $parts[] = 'Dòng này sẽ bị <strong>bỏ qua</strong> khi xác nhận import.';

        return implode(' ', $parts);
    }

    public static function forCodeWillUpdate(string $teacherCode): string
    {
        $code = e($teacherCode);

        return "Giáo lý viên mã <strong>{$code}</strong> đã có trong giáo xứ"
            . ' — thông tin sẽ được <strong>cập nhật</strong> khi xác nhận.';
    }

    public static function forAccountAlreadyExists(): string
    {
        return 'Hồ sơ <strong>đã có tài khoản</strong> — cột «Tạo tài khoản» sẽ bị <strong>bỏ qua</strong>'
            . ' (không tạo mới, không đổi mật khẩu).';
    }

    public static function forAccountWillCreateOnUpdate(): string
    {
        return 'Hồ sơ <strong>chưa có tài khoản</strong> và cột «Tạo tài khoản» = có'
            . ' — sẽ <strong>tạo tài khoản</strong> kèm cập nhật hồ sơ.'
            . ' Mật khẩu mặc định = chuỗi ngày sinh <code>ddmmyyyy</code>.';
    }

    public static function forInvalidCode(string $teacherCode): string
    {
        $code = e($teacherCode);

        return "Mã GLV <strong>{$code}</strong> không tồn tại trong giáo xứ."
            . ' → Kiểm tra lại mã, hoặc bỏ trống cột mã để hệ thống tự nhận diện theo tên thánh, họ tên và ngày sinh.'
            . ' Dòng này sẽ bị <strong>bỏ qua</strong> khi xác nhận import.';
    }

    public static function forDuplicateInFile(int $firstRowNumber): string
    {
        return "Trùng với <strong>dòng {$firstRowNumber}</strong> trong cùng file"
            . ' (cùng tên thánh, họ tên và ngày sinh).'
            . ' → Xoá bớt dòng lặp trong file Excel nếu đây không phải hai người khác nhau.'
            . ' Dòng này sẽ bị <strong>bỏ qua</strong> khi xác nhận import.';
    }

    public static function forPhoneMatch(string $phone, ?Teacher $teacher = null): string
    {
        $phoneLabel = e($phone);

        if ($teacher === null) {
            return "Số điện thoại <strong>{$phoneLabel}</strong> đã tồn tại trong giáo xứ.";
        }

        return "Số điện thoại <strong>{$phoneLabel}</strong> đang dùng cho"
            . ' <strong>' . e($teacher->full_name_with_saint) . '</strong>'
            . ' (mã <strong>' . e($teacher->teacher_code ?? '—') . '</strong>).'
            . ' → Vẫn import nếu đây là số dùng chung trong gia đình; nếu nhập sai, hãy sửa lại file.';
    }
}
