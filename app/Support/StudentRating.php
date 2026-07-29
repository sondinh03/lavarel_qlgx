<?php

namespace App\Support;

/**
 * Thang xếp loại học lực dùng chung cho bảng điểm, thống kê và file Excel.
 *
 * Thang này cố định theo hệ thống, không cấu hình theo giáo xứ. Mọi nơi cần
 * nhãn, màu hoặc khoảng điểm phải lấy từ đây để các trang không lệch ngưỡng.
 */
class StudentRating
{
    /**
     * Quy tắc: min <= điểm < max, riêng mức cao nhất lấy trọn cả điểm 10.
     *
     * Các class Tailwind viết thẳng chuỗi (không ghép động) để trình biên dịch
     * CSS không loại bỏ chúng khi quét file.
     */
    private const LEVELS = [
        'XUAT_SAC' => [
            'min'   => 9.5,
            'max'   => 10,
            'label' => 'Xuất sắc',
            'hex'   => '#10b981',
            'badge' => 'bg-emerald-50/80 text-emerald-700',
            'dot'   => 'bg-emerald-500',
        ],
        'GIOI' => [
            'min'   => 8.0,
            'max'   => 9.5,
            'label' => 'Giỏi',
            'hex'   => '#3b82f6',
            'badge' => 'bg-blue-50/80 text-blue-700',
            'dot'   => 'bg-blue-500',
        ],
        'KHA' => [
            'min'   => 6.5,
            'max'   => 8.0,
            'label' => 'Khá',
            'hex'   => '#f59e0b',
            'badge' => 'bg-amber-50/80 text-amber-700',
            'dot'   => 'bg-amber-400',
        ],
        'TRUNG_BINH' => [
            'min'   => 5.0,
            'max'   => 6.5,
            'label' => 'Trung bình',
            'hex'   => '#eab308',
            'badge' => 'bg-yellow-50/80 text-yellow-700',
            'dot'   => 'bg-yellow-400',
        ],
        'YEU' => [
            'min'   => 3.5,
            'max'   => 5.0,
            'label' => 'Yếu',
            'hex'   => '#f97316',
            'badge' => 'bg-orange-50/80 text-orange-700',
            'dot'   => 'bg-orange-500',
        ],
        'KEM' => [
            'min'   => 0,
            'max'   => 3.5,
            'label' => 'Kém',
            'hex'   => '#ef4444',
            'badge' => 'bg-red-50/80 text-red-700',
            'dot'   => 'bg-red-500',
        ],
    ];

    public const BADGE_CLASS_NONE = 'bg-slate-50/80 text-slate-400';

    /**
     * @return array<string, array{min: float|int, max: float|int, label: string, hex: string, badge: string, dot: string}>
     */
    public static function levels(): array
    {
        return self::LEVELS;
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::LEVELS);
    }

    public static function keyFor(?float $average): ?string
    {
        if ($average === null || $average < 0) {
            return null;
        }

        foreach (self::LEVELS as $key => $level) {
            $inRange = $average >= $level['min']
                && ($average < $level['max'] || ($level['max'] >= 10 && $average <= 10));

            if ($inRange) {
                return $key;
            }
        }

        return null;
    }

    public static function labelFor(?float $average, string $default = ''): string
    {
        $key = self::keyFor($average);

        return $key === null ? $default : self::LEVELS[$key]['label'];
    }

    public static function labelForKey(?string $key, string $default = ''): string
    {
        return isset(self::LEVELS[$key]) ? self::LEVELS[$key]['label'] : $default;
    }

    public static function badgeClassFor(?float $average): string
    {
        $key = self::keyFor($average);

        return $key === null ? self::BADGE_CLASS_NONE : self::LEVELS[$key]['badge'];
    }

    public static function hexFor(?float $average): ?string
    {
        $key = self::keyFor($average);

        return $key === null ? null : self::LEVELS[$key]['hex'];
    }

    /**
     * Thang xếp loại kèm mô tả khoảng điểm, dùng cho tab cách tính điểm.
     *
     * @return array<int, array{key: string, label: string, badge: string, dot: string, range: string}>
     */
    public static function scale(): array
    {
        $scale = [];

        foreach (self::LEVELS as $key => $level) {
            $min = self::formatScore((float) $level['min']);
            $max = self::formatScore((float) $level['max']);

            if ($level['max'] >= 10) {
                $range = 'Từ ' . $min . ' đến 10';
            } elseif ($level['min'] <= 0) {
                $range = 'Dưới ' . $max;
            } else {
                $range = 'Từ ' . $min . ' đến dưới ' . $max;
            }

            $scale[] = [
                'key'   => $key,
                'label' => $level['label'],
                'badge' => $level['badge'],
                'dot'   => $level['dot'],
                'range' => $range,
            ];
        }

        return $scale;
    }

    /**
     * Chú giải gọn cho biểu đồ thống kê: nhãn + khoảng điểm dạng ngắn.
     *
     * @return array<int, array{key: string, label: string, dot: string, range: string}>
     */
    public static function legend(): array
    {
        $legend = [];

        foreach (self::LEVELS as $key => $level) {
            $legend[] = [
                'key'   => $key,
                'label' => $level['label'],
                'dot'   => $level['dot'],
                'range' => self::formatScore((float) $level['min'])
                    . '–' . self::formatScore((float) $level['max']),
            ];
        }

        return $legend;
    }

    private static function formatScore(float $value): string
    {
        if ($value <= 0) {
            return '0';
        }

        if ($value >= 10) {
            return '10';
        }

        return number_format($value, 1, ',', '');
    }
}
