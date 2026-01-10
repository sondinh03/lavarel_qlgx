<?php

namespace App\Support;

use App\Models\User;

class AuthUser
{
    public static function user(): ?User
    {
        return auth('backpack')->user()
            ?? auth()->user();
    }
}