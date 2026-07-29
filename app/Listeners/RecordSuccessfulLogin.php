<?php

namespace App\Listeners;

use App\Models\SystemMetricDaily;
use App\Models\User;
use App\Models\UserLoginEvent;
use App\Services\Admin\SystemOverviewService;
use Illuminate\Auth\Events\Login;

class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (! $user instanceof User) {
            return;
        }

        $now = now();

        UserLoginEvent::query()->create([
            'user_id'    => $user->id,
            'parish_id'  => $user->parish_id,
            'guard'      => $event->guard ?: 'web',
            'ip'         => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 512) ?: null,
            'created_at' => $now,
        ]);

        $user->forceFill(['last_login_at' => $now])->saveQuietly();

        SystemMetricDaily::bump($now->toDateString(), [
            'logins' => 1,
        ]);

        app(SystemOverviewService::class)->forget();
    }
}
