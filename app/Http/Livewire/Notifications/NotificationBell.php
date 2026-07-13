<?php

namespace App\Http\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $limit = 10;

    protected $listeners = [
        'notificationUpdated' => '$refresh',
    ];

    public function markAsRead(string $id): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification && $notification->unread()) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $user->unreadNotifications->markAsRead();
        $this->emit('toast', 'message', 'Đã đánh dấu tất cả là đã đọc.');
    }

    public function openNotification(string $id)
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if (! $notification) {
            return;
        }

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        $url = $notification->data['url'] ?? null;
        if ($url) {
            return redirect()->to($url);
        }
    }

    public function render()
    {
        $user = Auth::user();

        $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
        $notifications = $user
            ? $user->notifications()->latest()->limit($this->limit)->get()
            : collect();

        return view('livewire.notifications.notification-bell', [
            'unreadCount'   => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
