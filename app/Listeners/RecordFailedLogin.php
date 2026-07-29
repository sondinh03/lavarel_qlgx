<?php

namespace App\Listeners;

use App\Models\SystemMetricDaily;
use App\Services\Admin\SystemOverviewService;
use Illuminate\Auth\Events\Failed;

class RecordFailedLogin
{
    public function handle(Failed $event): void
    {
        SystemMetricDaily::bump(now()->toDateString(), [
            'failed_logins' => 1,
        ]);

        app(SystemOverviewService::class)->forget();
    }
}
