<?php

namespace App\Listeners;

use App\Events\MarriageCreated;

/**
 * Reserved for async side-effects (notifications, exports, etc.).
 * Core marriage + family processing lives in MarriageService.
 */
class CreateFamilyOnMarriage
{
    public function handle(MarriageCreated $event): void
    {
        // Intentionally empty — MarriageService::processValidMarriage runs in CreateMarriageFromAnnouncementAction.
    }
}
