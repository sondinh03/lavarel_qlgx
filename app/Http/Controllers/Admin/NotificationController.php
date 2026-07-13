<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = backpack_user();
        abort_unless($user, 403);

        $notifications = $user->notifications()->latest()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('vendor.backpack.base.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
            'title'         => 'Thông báo',
        ]);
    }

    public function open(string $id): RedirectResponse
    {
        $user = backpack_user();
        abort_unless($user, 403);

        $notification = $user->notifications()->where('id', $id)->firstOrFail();

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        $url = $notification->data['url'] ?? null;

        if ($url) {
            return redirect()->to($url);
        }

        return redirect()->route('backpack.notifications.index');
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $user = backpack_user();
        abort_unless($user, 403);

        $notification = $user->notifications()->where('id', $id)->firstOrFail();

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        return back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        $user = backpack_user();
        abort_unless($user, 403);

        $user->unreadNotifications->markAsRead();

        \Alert::success('Đã đánh dấu tất cả thông báo là đã đọc.')->flash();

        return back();
    }
}
