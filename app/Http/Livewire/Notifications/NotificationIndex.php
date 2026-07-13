<?php

namespace App\Http\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->isSuperAdmin()) {
            $this->redirect(route('backpack.notifications.index'));
        }
    }

    public function markAsRead(string $id): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification && $notification->unread()) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $user->unreadNotifications->markAsRead();
        $this->emit('toast', 'message', 'Đã đánh dấu tất cả là đã đọc.');
    }

    public function openNotification(string $id)
    {
        $user = Auth::user();
        abort_unless($user, 403);

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
        abort_unless($user, 403);

        $layout = match (true) {
            $user->usesCatechistLayout() => 'frontend.layout.catechist',
            $user->canManageParishioners() && ! $user->canManageCatechism() => 'frontend.layout.parishioner',
            $user->canManageCatechism() => 'frontend.layout.main',
            $user->canManageParishioners() => 'frontend.layout.parishioner',
            default => 'frontend.layout.landing',
        };

        return view('livewire.notifications.notification-index', [
            'notifications' => $user->notifications()->latest()->paginate(20),
            'unreadCount'   => $user->unreadNotifications()->count(),
        ])->extends($layout)->section('content');
    }
}
