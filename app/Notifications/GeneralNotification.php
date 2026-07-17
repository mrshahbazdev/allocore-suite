<?php

namespace App\Notifications;

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
        public string $type = 'info'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
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
}
