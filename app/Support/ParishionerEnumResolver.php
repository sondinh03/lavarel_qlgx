<?php

namespace App\Support;

class ParishionerEnumResolver
{
    public static function resolve(string $configKey, ?string $label): ?int
    {
        $label = trim($label ?? '');
        if ($label === '') {
            return null;
        }

        $options = config("parishioner.{$configKey}", []);
        $needle  = mb_strtolower($label, 'UTF-8');

        foreach ($options as $id => $name) {
            if (mb_strtolower((string) $name, 'UTF-8') === $needle) {
                return (int) $id;
            }
        }

        return is_numeric($label) ? (int) $label : null;
    }

    public static function label(string $configKey, mixed $id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        return config("parishioner.{$configKey}.{$id}");
    }

    public static function parseMarriedStatus(?string $value): int
    {
        $v = mb_strtolower(trim($value ?? ''), 'UTF-8');

        if (in_array($v, ['đã kết hôn', 'da ket hon', 'ket hon', 'đã lập gia đình', 'married', '1'], true)) {
            return 1;
        }
        if (in_array($v, ['góa', 'goa', 'widowed', '2'], true)) {
            return 2;
        }
        if (in_array($v, ['ly hôn', 'ly hon', 'divorced', '3'], true)) {
            return 3;
        }

        return 0;
    }

    public static function parseBoolean(?string $value): bool
    {
        return in_array(mb_strtolower(trim($value ?? ''), 'UTF-8'), ['có', 'co', 'yes', '1'], true);
    }

    public static function parseGender(?string $value): string
    {
        $v = mb_strtolower(trim($value ?? ''), 'UTF-8');

        return in_array($v, ['nữ', 'nu', 'female', 'f', '0'], true) ? 'female' : 'male';
    }

    public static function parseMarriageRecordStatus(?string $value): string
    {
        $v = mb_strtolower(trim($value ?? ''), 'UTF-8');

        return match (true) {
            in_array($v, ['hợp lệ', 'hop le', 'valid', '1'], true)       => 'valid',
            in_array($v, ['bất hợp lệ', 'bat hop le', 'invalid', '2'], true) => 'invalid',
            in_array($v, ['góa', 'goa', 'widowed', '3'], true)           => 'widowed',
            in_array($v, ['ly hôn', 'ly hon', 'divorced', '4'], true)    => 'divorced',
            default                                                        => 'valid',
        };
    }

    public static function marriageStatusToLegacyLabel(?string $status): string
    {
        return match ($status) {
            'valid'    => 'Hợp pháp',
            'invalid'  => 'Không theo phép đạo',
            'widowed'  => 'Góa',
            'divorced' => 'Ly dị',
            default    => '',
        };
    }

    public static function marriedToLegacyLabel(int $married): string
    {
        return match ($married) {
            1       => 'Đã lập gia đình',
            default => 'Chưa lập gia đình',
        };
    }

    public static function rowKey(string $lastName, string $firstName, ?string $birthday): string
    {
        $birthday = $birthday ? ExcelDateParser::parse($birthday) : '';

        return mb_strtolower(trim($lastName . ' ' . $firstName), 'UTF-8') . '_' . ($birthday ?? '');
    }
}
