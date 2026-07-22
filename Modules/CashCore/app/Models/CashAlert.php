<?php

namespace Modules\CashCore\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\CashCore\Models\Concerns\BelongsToCurrentTeam;

class CashAlert extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'user_id', 'alert_type', 'severity', 'title',
        'message', 'is_read', 'is_dismissed', 'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $table = 'cashcore_alerts';

    public function scopeUnread($query)
    {
        return $query->where('is_read', false)->where('is_dismissed', false);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
