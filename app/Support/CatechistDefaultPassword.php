<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeImmutable;
use DateTimeInterface;

class CatechistDefaultPassword
{
    /**
     * Mật khẩu mặc định giáo lý viên = chuỗi ngày sinh dạng ddmmyyyy
     * (vd: 15/08/2000 → 15082000). Không có ngày sinh → fallback config.
     */
    public static function fromBirthday(mixed $birthday): string
    {
        $date = self::normalize($birthday);

        if ($date) {
            return $date->format('dmY');
        }

        return (string) config('qlgx.catechist_default_password', '12345678');
    }

    private static function normalize(mixed $birthday): ?CarbonInterface
    {
        if ($birthday === null || $birthday === '') {
            return null;
        }

        if ($birthday instanceof CarbonInterface) {
            return $birthday;
        }

        if ($birthday instanceof DateTimeInterface) {
            return Carbon::instance(DateTimeImmutable::createFromInterface($birthday));
        }

        try {
            if (is_string($birthday) && preg_match('/^\d{4}-\d{2}-\d{2}/', $birthday)) {
                return Carbon::createFromFormat('Y-m-d', substr($birthday, 0, 10))->startOfDay();
            }

            if (is_numeric($birthday)) {
                $parsed = ExcelDateParser::parse($birthday);

                return $parsed ? Carbon::parse($parsed)->startOfDay() : null;
            }

            if (is_string($birthday) && str_contains($birthday, '/')) {
                $date = Carbon::createFromFormat('d/m/Y', trim($birthday));
                $errors = Carbon::getLastErrors();
                if (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
                    return null;
                }

                return $date->startOfDay();
            }

            return Carbon::parse($birthday)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
