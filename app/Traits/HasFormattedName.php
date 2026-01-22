<?php

namespace App\Traits;

trait HasFormattedName
{
    /**
     * Format chuỗi: viết hoa chữ cái đầu mỗi từ (UTF-8 safe)
     */
    protected function formatName(string $value): string
    {
        $value = trim($value);

        // Bước 1 + 2: lowercase toàn bộ rồi ucwords
        $value = ucwords(
            mb_strtolower($value, 'UTF-8')
        );

        // Bước 3: Viết hoa chữ cái ngay sau số (1a -> 1A)
        $value = preg_replace_callback(
            '/(\d)([a-z])/u',
            fn($m) => $m[1] . mb_strtoupper($m[2], 'UTF-8'),
            $value
        );

        return $value;
    }

    /**
     * Mutator cho field name
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->formatName($value);
    }
}
