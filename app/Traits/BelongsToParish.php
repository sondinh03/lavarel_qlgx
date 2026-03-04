<?php

namespace App\Traits;

use App\Scopes\ParishScope;

trait BelongsToParish
{
    protected static function bootBelongsToParish(): void
    {
        static::addGlobalScope(new ParishScope);
    }
}