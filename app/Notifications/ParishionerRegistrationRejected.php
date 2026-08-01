<?php

namespace App\Notifications;

use App\Models\ParishionerRegistrationRequest;
use App\Notifications\Concerns\SendsWebPush;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParishionerRegistrationRejected extends Notification
{
    use Queueable;
    use SendsWebPush;

    public function __construct(public ParishionerRegistrationRequest $request)
    {
    }

    public function via($notifiable): array
    {
        return $this->viaWebPushChannels(['database']);
    }

    public function toArray($notifiable): array
    {
        $name = $this->request->submitted_name ?: 'Một hồ sơ';

        return [
            'title' => 'Đã từ chối đăng ký giáo dân',
            'body'  => "{$name} (mã {$this->request->reference_code}) đã bị từ chối.",
            'url'   => route('parishioners.registrations.show', $this->request),
            'level' => 'warning',
        ];
    }
}
