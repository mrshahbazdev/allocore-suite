<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject(__('Reset your password'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('You are receiving this email because we received a password reset request for your account (:email).', ['email' => $notifiable->getEmailForPasswordReset()]))
            ->action(__('Reset Password'), $url)
            ->line(__('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60)]))
            ->line(__('If you did not request a password reset, no further action is required.'));
    }
}
