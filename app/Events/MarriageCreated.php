<?php

namespace App\Events;

use App\DTOs\MarriageProcessResult;
use App\Models\Marriage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MarriageCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Marriage $marriage,
        public ?MarriageProcessResult $result = null,
    ) {}
}
