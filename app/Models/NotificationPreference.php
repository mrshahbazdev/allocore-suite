<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'email',
        'in_app',
        'push',
        'slack',
        'slack_webhook',
    ];

    protected function casts(): array
    {
        return [
            'email' => 'boolean',
            'in_app' => 'boolean',
            'push' => 'boolean',
            'slack' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
