<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CatechismBoardAnnouncement extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $audienceLabel,
        public ?string $url = null,
        public string $senderName = '',
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $from = $this->senderName !== '' ? $this->senderName : 'Ban giáo lý';

        return [
            'title' => $this->title,
            'body'  => $this->body,
            'url'   => $this->url ?: route('notifications.index'),
            'level' => 'info',
            'meta'  => [
                'type'     => 'catechism_board',
                'audience' => $this->audienceLabel,
                'from'     => $from,
            ],
        ];
    }
}
