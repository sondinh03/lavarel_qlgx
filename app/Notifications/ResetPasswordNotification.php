<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expire = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Đặt lại mật khẩu — ' . config('app.name'))
            ->greeting('Xin chào!')
            ->line('Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.')
            ->action('Đặt lại mật khẩu', $url)
            ->line("Liên kết có hiệu lực trong {$expire} phút.")
            ->line('Nếu bạn không gửi yêu cầu này, hãy bỏ qua email.');
    }
}
