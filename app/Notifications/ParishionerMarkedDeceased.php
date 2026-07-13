<?php

namespace App\Notifications;

use App\Models\Parishioner;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParishionerMarkedDeceased extends Notification
{
    use Queueable;

    public function __construct(public Parishioner $parishioner)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $name = trim(($this->parishioner->last_name ?? '') . ' ' . ($this->parishioner->first_name ?? ''))
            ?: 'Một giáo dân';

        return [
            'title' => 'Cập nhật giáo dân qua đời',
            'body'  => "{$name} đã được ghi nhận thông tin tử vong.",
            'url'   => route('parishioners.show', $this->parishioner),
            'level' => 'warning',
        ];
    }
}
