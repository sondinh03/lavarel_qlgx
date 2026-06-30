<?php

namespace App\Support;

use App\Models\Teacher;
use App\Models\User;

class UserAccountEmailResolver
{
    public static function phoneLoginDomain(): string
    {
        return (string) config('qlgx.phone_login_domain', 'giaoly.local');
    }

    public static function isEmail(string $input): bool
    {
        return str_contains(trim($input), '@');
    }

    public static function normalizePhone(string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', trim($input));

        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '84') && strlen($digits) >= 11) {
            $digits = '0' . substr($digits, 2);
        }

        if (! preg_match('/^0\d{8,10}$/', $digits)) {
            return null;
        }

        return $digits;
    }

    public static function accountEmailFromPhone(string $phone): string
    {
        $normalized = self::normalizePhone($phone);

        if ($normalized === null) {
            throw new \InvalidArgumentException('Số điện thoại không hợp lệ.');
        }

        return $normalized . '@' . self::phoneLoginDomain();
    }

    public static function isSyntheticEmail(string $email): bool
    {
        $domain = self::phoneLoginDomain();

        return str_ends_with(strtolower(trim($email)), '@' . $domain);
    }

    public static function resolveLoginIdentifier(string $input): string
    {
        $input = trim($input);

        if ($input === '') {
            return '';
        }

        if (self::isEmail($input)) {
            return strtolower($input);
        }

        $normalized = self::normalizePhone($input);

        if ($normalized !== null) {
            return $normalized . '@' . self::phoneLoginDomain();
        }

        return strtolower($input);
    }

    public static function resolveAccountEmail(?string $email, ?string $phone): string
    {
        $email = trim((string) $email);

        if ($email !== '') {
            return strtolower($email);
        }

        if (trim((string) $phone) === '') {
            throw new \InvalidArgumentException('Cần có SĐT hoặc email để tạo tài khoản.');
        }

        return self::accountEmailFromPhone($phone);
    }

    public static function findUserEmailByPhone(string $phone): ?string
    {
        $normalized = self::normalizePhone($phone);

        if ($normalized === null) {
            return null;
        }

        $teachers = Teacher::query()
            ->whereNotNull('user_id')
            ->whereNotNull('phone_number')
            ->with('user:id,email')
            ->get(['id', 'user_id', 'phone_number']);

        foreach ($teachers as $teacher) {
            if (self::normalizePhone($teacher->phone_number ?? '') === $normalized) {
                return $teacher->user?->email
                    ? strtolower($teacher->user->email)
                    : null;
            }
        }

        return null;
    }

    public static function findUserByPhone(string $phone): ?User
    {
        $email = self::findUserEmailByPhone($phone);

        return $email ? User::where('email', $email)->first() : null;
    }

    /**
     * @return array{email: ?string, error: ?string}
     */
    public static function resolveForPasswordReset(string $input): array
    {
        $raw = trim($input);

        if ($raw === '') {
            return [
                'email' => null,
                'error' => 'Vui lòng nhập email hoặc số điện thoại.',
            ];
        }

        $resolved = self::resolveLoginIdentifier($raw);

        if (! self::isSyntheticEmail($resolved)) {
            return ['email' => $resolved, 'error' => null];
        }

        $phoneForLookup = $raw;
        if (self::isEmail($raw) && self::isSyntheticEmail($resolved)) {
            $localPart = explode('@', $resolved, 2)[0] ?? '';
            if (self::normalizePhone($localPart) !== null) {
                $phoneForLookup = $localPart;
            }
        }

        $realEmail = self::findUserEmailByPhone($phoneForLookup);

        if ($realEmail && ! self::isSyntheticEmail($realEmail)) {
            return ['email' => $realEmail, 'error' => null];
        }

        return [
            'email' => null,
            'error' => 'Tài khoản đăng nhập bằng SĐT không thể khôi phục qua email. Vui lòng liên hệ quản trị xứ để được cấp lại mật khẩu.',
        ];
    }
}
