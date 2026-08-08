<?php

namespace App\Support;

class ExcelString
{
    /**
     * Xóa khoảng trắng đầu/cuối (kể cả NBSP, BOM, Unicode spaces từ Excel).
     */
    public static function trim(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = (string) $value;

        // BOM đầu chuỗi
        $value = preg_replace('/^\x{FEFF}/u', '', $value) ?? $value;

        // Unicode spaces → space thường (NBSP, thin spaces, ideographic space...)
        $value = preg_replace(
            '/[\x{00A0}\x{1680}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}\x{FEFF}]/u',
            ' ',
            $value
        ) ?? $value;

        return trim($value);
    }

    /**
     * Trim + gộp nhiều khoảng trắng liên tiếp (dùng khi so khớp tên thánh / giáo họ).
     */
    public static function clean(mixed $value): string
    {
        $value = self::trim($value);

        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }

    /**
     * clean() rồi đưa về chữ thường UTF-8 (lookup danh mục không phân biệt hoa thường).
     */
    public static function lower(mixed $value): string
    {
        return mb_strtolower(self::clean($value), 'UTF-8');
    }
}
