<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookCall extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_status',
        'response_body',
        'attempts',
        'sent_at',
        'failed_at',
        'failure_message',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response_status' => 'integer',
            'attempts' => 'integer',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function markSent(int $status, string $body): void
    {
        $this->update([
            'attempts' => $this->attempts + 1,
            'response_status' => $status,
            'response_body' => $body,
            'sent_at' => now(),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'attempts' => $this->attempts + 1,
            'failed_at' => now(),
            'failure_message' => $message,
        ]);
    }

    public function isSuccessful(): bool
    {
        return $this->response_status !== null && $this->response_status >= 200 && $this->response_status < 300;
    }
}
