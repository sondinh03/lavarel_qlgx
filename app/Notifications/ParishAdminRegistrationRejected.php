<?php

namespace App\Notifications;

use App\Models\ParishAdminRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParishAdminRegistrationRejected extends Notification
{
    use Queueable;

    public function __construct(public ParishAdminRegistrationRequest $request)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $parish = $this->request->parishDisplayName();
        $message = (new MailMessage)
            ->subject('Kết quả đăng ký quản trị xứ — ' . config('app.name'))
            ->greeting('Xin chào ' . ($this->request->name ?: 'bạn') . '!')
            ->line("Yêu cầu đăng ký quản trị giáo xứ {$parish} của bạn chưa được chấp thuận.");

        if (trim((string) $this->request->rejection_reason) !== '') {
            $message->line('Lý do: ' . $this->request->rejection_reason);
        }

        return $message
            ->action('Đăng ký lại', route('parish-admin.register.public'))
            ->line('Bạn có thể kiểm tra lại thông tin và gửi một yêu cầu mới.')
            ->salutation('Trân trọng, ' . config('app.name'));
    }
}
