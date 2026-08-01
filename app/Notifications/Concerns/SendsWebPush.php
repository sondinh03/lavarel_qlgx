<?php

namespace App\Notifications\Concerns;

use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

trait SendsWebPush
{
    /**
     * Append WebPush channel when VAPID keys are configured.
     *
     * @param  array<int, string|class-string>  $channels
     * @return array<int, string|class-string>
     */
    protected function viaWebPushChannels(array $channels = ['database']): array
    {
        if (config('webpush.vapid.public_key') && config('webpush.vapid.private_key')) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $data = method_exists($this, 'toArray') ? $this->toArray($notifiable) : [];
        $url = $data['url'] ?? url('/thong-bao');

        return (new WebPushMessage)
            ->title($data['title'] ?? 'Thông báo')
            ->body($data['body'] ?? '')
            ->icon('/web-app-manifest-192x192.png')
            ->badge('/web-app-manifest-192x192.png')
            ->data([
                'url'   => $url,
                'level' => $data['level'] ?? 'info',
            ])
            ->tag('mvgx-' . md5(($data['title'] ?? '') . '|' . ($data['body'] ?? '') . '|' . ($url ?? '')))
            ->options(['TTL' => 86400]);
    }
}
