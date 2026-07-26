<?php

namespace App\Support;

/**
 * Palette màu chủ đạo cho thẻ học sinh / GLV (CR80).
 */
class CardTheme
{
    public const DEFAULT = 'green';

    /**
     * @return array<string, array>
     */
    public static function all(): array
    {
        return [
            'green' => self::build(
                'Xanh lá',
                '#34C759',
                '#57C37F',
                '#2AA14A',
                '#145224',
                '#F3FBF6',
                '#EAF7EF',
                [52, 199, 89]
            ),
            'blue' => self::build(
                'Xanh dương',
                '#007AFF',
                '#5AC8FA',
                '#0056CC',
                '#003D99',
                '#F0F7FF',
                '#E5F0FF',
                [0, 122, 255]
            ),
            'yellow' => self::build(
                'Vàng nhạt',
                '#F5D76E',
                '#F9E79F',
                '#D4AC0D',
                '#7D6608',
                '#FFFDF5',
                '#FBF5E0',
                [245, 215, 110]
            ),
            'red' => self::build(
                'Đỏ',
                '#FF3B30',
                '#FF6961',
                '#D70015',
                '#8B0000',
                '#FFF5F5',
                '#FFEBEB',
                [255, 59, 48]
            ),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isValid(?string $key): bool
    {
        return $key !== null && isset(self::all()[$key]);
    }

    public static function normalize(?string $key): string
    {
        return self::isValid($key) ? $key : self::DEFAULT;
    }

    /**
     * @return array<string, string>
     */
    public static function resolve(?string $key): array
    {
        $themes = self::all();

        return $themes[self::normalize($key)];
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     * @return array<string, string>
     */
    private static function build(
        string $label,
        string $primary,
        string $light,
        string $dark,
        string $deep,
        string $bgFrom,
        string $bgTo,
        array $rgb
    ): array {
        $r = $rgb[0];
        $g = $rgb[1];
        $b = $rgb[2];

        return [
            'label'        => $label,
            'primary'      => $primary,
            'light'        => $light,
            'dark'         => $dark,
            'deep'         => $deep,
            'bgFrom'       => $bgFrom,
            'bgTo'         => $bgTo,
            'headerBorder' => "rgba({$r}, {$g}, {$b}, 0.14)",
            'ringBorder'   => "rgba({$r}, {$g}, {$b}, 0.35)",
            'ringGlow'     => "rgba({$r}, {$g}, {$b}, 0.12)",
            'divider'      => "rgba({$r}, {$g}, {$b}, 0.45)",
            'avatarBg'     => $bgFrom,
        ];
    }
}
