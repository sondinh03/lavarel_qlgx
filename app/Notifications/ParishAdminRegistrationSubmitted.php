<?php

namespace App\Notifications;

use App\Models\ParishAdminRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParishAdminRegistrationSubmitted extends Notification
{
    use Queueable;

    public function __construct(public ParishAdminRegistrationRequest $request)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $parish = $this->request->parishDisplayName();
        $who = $this->request->name ?: $this->request->email;

        return [
            'title' => 'Đăng ký quản trị xứ mới',
            'body'  => "{$who} đăng ký quản trị giáo xứ {$parish}.",
            'url'   => url(config('backpack.base.route_prefix', 'admin') . '/parish-admin-registration/' . $this->request->id . '/show'),
            'level' => 'info',
        ];
    }
}
