<?php

namespace App\Support;

class VietnamAddressResolver
{
    private static ?array $provinces = null;
    private static ?array $wards     = null;

    public static function provinceName(mixed $idOrName): string
    {
        if (empty($idOrName)) {
            return '';
        }

        if (!is_numeric($idOrName)) {
            return trim((string) $idOrName);
        }

        self::loadProvinces();

        return self::$provinces[$idOrName] ?? self::$provinces[(string) $idOrName] ?? '';
    }

    public static function wardName(mixed $idOrName): string
    {
        if (empty($idOrName)) {
            return '';
        }

        if (!is_numeric($idOrName)) {
            return trim((string) $idOrName);
        }

        self::loadWards();

        foreach (self::$wards as $ward) {
            if (($ward['xaid'] ?? null) == $idOrName) {
                return $ward['name'] ?? '';
            }
        }

        return '';
    }

    public static function resolveWardId(?string $name): ?int
    {
        $name = trim($name ?? '');
        if ($name === '') {
            return null;
        }

        self::loadWards();
        $needle = mb_strtolower($name, 'UTF-8');

        foreach (self::$wards as $ward) {
            if (mb_strtolower($ward['name'] ?? '', 'UTF-8') === $needle) {
                return (int) ($ward['xaid'] ?? 0) ?: null;
            }
        }

        return is_numeric($name) ? (int) $name : null;
    }

    public static function resolveProvinceKey(?string $name): ?string
    {
        $name = trim($name ?? '');
        if ($name === '') {
            return null;
        }

        self::loadProvinces();
        $needle = mb_strtolower($name, 'UTF-8');

        foreach (self::$provinces as $key => $label) {
            if (mb_strtolower($label, 'UTF-8') === $needle) {
                return (string) $key;
            }
        }

        return $name;
    }

    public static function formatAddressLine(?string $detail, mixed $ward, mixed $province, bool $trailingComma = false): string
    {
        $parts = array_filter([
            $detail ? rtrim($detail, ',') . ($trailingComma ? ',' : '') : null,
            ($w = self::wardName($ward)) ? $w . ($trailingComma ? ',' : '') : null,
            self::provinceName($province),
        ]);

        return implode(' ', $parts);
    }

    private static function loadProvinces(): void
    {
        if (self::$provinces !== null) {
            return;
        }

        $tinh_thanhpho = [];
        include resource_path('cities/tinh_thanhpho.php');
        self::$provinces = $tinh_thanhpho ?? [];
    }

    private static function loadWards(): void
    {
        if (self::$wards !== null) {
            return;
        }

        $xa_phuong_thitran = [];
        include resource_path('cities/xa_phuong_thitran.php');
        self::$wards = $xa_phuong_thitran ?? [];
    }
}
