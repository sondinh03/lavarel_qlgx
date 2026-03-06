<?php

namespace App\Support;

class ParishCodeGenerator
{
    /**
     * Generate mã gợi ý 3 ký tự từ tên giáo xứ
     * VD: "Hoành Đông" → "HDO"
     */
    public static function suggest(string $name): string
    {
        // Bước 1: Chuyển về không dấu
        $name = static::removeAccents($name);

        // Bước 2: Uppercase, tách từ
        $words = explode(' ', strtoupper(trim($name)));
        $words = array_filter($words); // bỏ khoảng trắng thừa

        $code = match (count($words)) {
            // 1 từ: lấy 3 ký tự đầu — "CHU" → "CHU"
            1 => substr($words[0], 0, 3),

            // 2 từ: chữ đầu từ 1 + 2 ký tự đầu từ 2 — "BUI CHU" → "BCH"
            2 => substr($words[0], 0, 1) . substr($words[1], 0, 2),

            // 3 từ+: chữ đầu mỗi từ — "HOANH DONG NAM" → "HDN"
            default => substr($words[0], 0, 1)
                     . substr($words[1], 0, 1)
                     . substr($words[2], 0, 1),
        };

        return substr($code, 0, 3); // đảm bảo tối đa 3 ký tự
    }

    private static function removeAccents(string $str): string
    {
        $accents = [
            'à','á','â','ã','ä','å','ā','ă','ą',
            'è','é','ê','ë','ē','ĕ','ě',
            'ì','í','î','ï','ī',
            'ò','ó','ô','õ','ö','ō','ŏ',
            'ù','ú','û','ü','ū',
            'ý','ÿ',
            'ñ','ç','ð','ß','þ',
            // Tiếng Việt
            'à','á','â','ã','è','é','ê','ì','í',
            'ò','ó','ô','õ','ù','ú','ý',
            'ă','ắ','ặ','ằ','ẳ','ẵ',
            'â','ấ','ậ','ầ','ẩ','ẫ',
            'đ',
            'ê','ế','ệ','ề','ể','ễ',
            'ô','ố','ộ','ồ','ổ','ỗ',
            'ơ','ớ','ợ','ờ','ở','ỡ',
            'ư','ứ','ự','ừ','ử','ữ',
            'ỳ','ỵ','ỷ','ỹ',
            'À','Á','Â','Ã','È','É','Ê','Ì','Í',
            'Ò','Ó','Ô','Õ','Ù','Ú','Ý',
            'Ă','Ắ','Ặ','Ằ','Ẳ','Ẵ',
            'Â','Ấ','Ậ','Ầ','Ẩ','Ẫ',
            'Đ',
            'Ê','Ế','Ệ','Ề','Ể','Ễ',
            'Ô','Ố','Ộ','Ồ','Ổ','Ỗ',
            'Ơ','Ớ','Ợ','Ờ','Ở','Ỡ',
            'Ư','Ứ','Ự','Ừ','Ử','Ữ',
            'Ỳ','Ỵ','Ỷ','Ỹ',
        ];

        $replace = [
            'a','a','a','a','a','a','a','a','a',
            'e','e','e','e','e','e','e',
            'i','i','i','i','i',
            'o','o','o','o','o','o','o',
            'u','u','u','u','u',
            'y','y',
            'n','c','d','ss','th',
            'a','a','a','a','e','e','e','i','i',
            'o','o','o','o','u','u','y',
            'a','a','a','a','a','a',
            'a','a','a','a','a','a',
            'd',
            'e','e','e','e','e','e',
            'o','o','o','o','o','o',
            'o','o','o','o','o','o',
            'u','u','u','u','u','u',
            'y','y','y','y',
            'A','A','A','A','E','E','E','I','I',
            'O','O','O','O','U','U','Y',
            'A','A','A','A','A','A',
            'A','A','A','A','A','A',
            'D',
            'E','E','E','E','E','E',
            'O','O','O','O','O','O',
            'O','O','O','O','O','O',
            'U','U','U','U','U','U',
            'Y','Y','Y','Y',
        ];

        return str_replace($accents, $replace, $str);
    }
}