<?php

namespace App\Notifications;

use App\Models\ParishAdminRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParishAdminRegistrationApproved extends Notification
{
    use Queueable;

    public function __construct(public ParishAdminRegistrationRequest $request)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $parish = $this->request->parishDisplayName();
        $roles = $this->roleLabels($notifiable);

        return (new MailMessage)
            ->subject('Yêu cầu đăng ký quản trị xứ đã được duyệt — ' . config('app.name'))
            ->greeting('Xin chào ' . ($this->request->name ?: 'bạn') . '!')
            ->line("Yêu cầu đăng ký quản trị giáo xứ {$parish} của bạn đã được duyệt.")
            ->line("Vai trò tài khoản: {$roles}.")
            ->line('Bạn có thể đăng nhập bằng email và mật khẩu đã đăng ký.')
            ->action('Đăng nhập', route('login'))
            ->line('Nếu cần hỗ trợ, vui lòng liên hệ quản trị hệ thống.')
            ->salutation('Trân trọng, ' . config('app.name'));
    }

    public function toArray($notifiable): array
    {
        $roles = $this->roleLabels($notifiable);

        return [
            'title' => 'Tài khoản quản trị đã được duyệt',
            'body'  => "Yêu cầu đăng ký quản trị xứ của bạn đã được duyệt với vai trò {$roles}. Bạn có thể đăng nhập ngay.",
            'url'   => route('login'),
            'level' => 'success',
        ];
    }

    private function roleLabels($notifiable): string
    {
        $catalog = config('parish-admin-registration.roles', []);
        $roleNames = method_exists($notifiable, 'getRoleNames')
            ? $notifiable->getRoleNames()->all()
            : ($this->request->requested_roles ?? []);

        $labels = collect($roleNames)
            ->map(fn ($role) => $catalog[$role]['label'] ?? $role)
            ->filter()
            ->implode(', ');

        return $labels !== '' ? $labels : 'Quản trị xứ';
    }
}
