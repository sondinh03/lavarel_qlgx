<?php

namespace App\DTOs;

use App\Models\Family;
use App\Models\Marriage;

class MarriageProcessResult
{
    /**
     * @param  array<int, string>  $warnings
     * @param  array<int, array<string, mixed>>  $auditLog
     */
    public function __construct(
        public Marriage $marriage,
        public ?Family $family = null,
        public array $warnings = [],
        public array $auditLog = [],
    ) {}
}
