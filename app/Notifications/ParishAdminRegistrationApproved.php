<?php

namespace App\Notifications;

use App\Models\ParishAdminRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParishAdminRegistrationApproved extends Notification
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
        return [
            'title' => 'Tài khoản quản trị đã được duyệt',
            'body'  => 'Yêu cầu đăng ký quản trị xứ của bạn đã được duyệt. Bạn có thể đăng nhập ngay.',
            'url'   => route('login'),
            'level' => 'success',
        ];
    }
}
