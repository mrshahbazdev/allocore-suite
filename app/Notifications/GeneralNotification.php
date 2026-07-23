<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\SlackWebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $subject,
        public string $body,
        public ?string $actionUrl = null,
        public ?string $actionText = null,
        public string $type = 'info',
        public string $preferenceType = 'general'
    ) {}

    public function via(object $notifiable): array
    {
        $preference = $notifiable instanceof User
            ? $notifiable->notificationPreference($this->preferenceType)
            : null;

        if (! $preference) {
            return ['database', 'mail'];
        }

        $channels = [];

        if ($preference->in_app) {
            $channels[] = 'database';
        }

        if ($preference->email) {
            $channels[] = 'mail';
        }

        if ($preference->slack && $preference->slack_webhook) {
            $channels[] = SlackWebhookChannel::class;
        }

        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->subject)
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line($this->body);

        if ($this->actionUrl) {
            $message->action($this->actionText ?? __('View'), $this->actionUrl);
        }

        return $message;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'subject' => $this->subject,
            'body' => $this->body,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
        ];
    }

    public function toSlackWebhook(object $notifiable): array
    {
        return [
            'text' => "*{$this->subject}*\n{$this->body}",
        ];
    }
}
