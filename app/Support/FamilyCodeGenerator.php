<?php

namespace App\Support;

use App\Models\Family;
use App\Models\ParishionerRegistrationRequest;

class FamilyCodeGenerator
{
    public static function generate(): string
    {
        do {
            $code = 'GD' . now()->format('ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (self::isTaken($code));

        return $code;
    }

    public static function isTaken(string $code): bool
    {
        return Family::where('code', $code)->exists()
            || ParishionerRegistrationRequest::where('reference_code', $code)->exists();
    }
}
