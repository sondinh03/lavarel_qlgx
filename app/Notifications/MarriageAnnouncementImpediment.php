<?php

namespace App\Notifications;

use App\Models\MarriageAnnouncement;
use App\Notifications\Concerns\SendsWebPush;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MarriageAnnouncementImpediment extends Notification
{
    use Queueable;
    use SendsWebPush;

    public function __construct(public MarriageAnnouncement $announcement)
    {
    }

    public function via($notifiable): array
    {
        return $this->viaWebPushChannels(['database']);
    }

    public function toArray($notifiable): array
    {
        $name = $this->announcement->name ?: 'Rao hôn phối';

        return [
            'title' => 'Rao hôn phối có ngăn trở',
            'body'  => "{$name} đang ở trạng thái có ngăn trở — cần xử lý.",
            'url'   => route('marriage-announcements.edit', $this->announcement->id),
            'level' => 'warning',
        ];
    }
}
