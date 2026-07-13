<?php

namespace App\Notifications;

use App\Models\MarriageAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MarriageAnnouncementImpediment extends Notification
{
    use Queueable;

    public function __construct(public MarriageAnnouncement $announcement)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
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
