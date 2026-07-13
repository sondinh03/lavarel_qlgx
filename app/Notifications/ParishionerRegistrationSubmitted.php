<?php

namespace App\Notifications;

use App\Models\ParishionerRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParishionerRegistrationSubmitted extends Notification
{
    use Queueable;

    public function __construct(public ParishionerRegistrationRequest $request)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $name = $this->request->submitted_name ?: 'Một gia đình';

        return [
            'title' => 'Đăng ký giáo dân chờ duyệt',
            'body'  => "{$name} vừa gửi đăng ký (mã {$this->request->reference_code}).",
            'url'   => route('parishioners.registrations.show', $this->request),
            'level' => 'info',
        ];
    }
}
