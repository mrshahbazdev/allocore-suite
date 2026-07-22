<?php

namespace Modules\LoopEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\LoopEngine\Models\Concerns\BelongsToCurrentTeam;

class WebhookLog extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'loopengine_webhook_logs';

    protected $fillable = [
        'team_id',
        'webhook_id',
        'event',
        'payload',
        'response_code',
        'response_body',
        'success',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'success' => 'boolean',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
    }
}
