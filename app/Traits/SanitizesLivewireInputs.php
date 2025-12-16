<?php

namespace App\Traits;

trait SanitizesLivewireInputs
{
    /**
     * Sanitize integer input (nullable)
     */
    protected function sanitizeNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Sanitize integer input (with default)
     */
    protected function sanitizeInt($value, int $default): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Sanitize string input
     */
    protected function sanitizeString($value, string $default = ''): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return trim((string) $value);
    }

    /**
     * Sanitize boolean input
     */
    protected function sanitizeBool($value, bool $default = false): bool
    {
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Sanitize value from allowed options
     */
    protected function sanitizeFromOptions($value, array $options, $default)
    {
        return in_array($value, $options, true) ? $value : $default;
    }
}