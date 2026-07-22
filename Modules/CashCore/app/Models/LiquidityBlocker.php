<?php

namespace Modules\CashCore\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\CashCore\Models\Concerns\BelongsToCurrentTeam;

class LiquidityBlocker extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'user_id', 'blocker_type', 'title', 'description',
        'blocked_amount', 'due_date', 'debtor_name', 'days_overdue',
        'status', 'action_items',
    ];

    protected $casts = [
        'blocked_amount' => 'float',
        'due_date' => 'date',
        'action_items' => 'array',
    ];

    protected $table = 'cashcore_liquidity_blockers';

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'in_progress']);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'open_invoice' => 'Open Invoice',
            'payment_terms' => 'Unfavorable Payment Terms',
            'inventory' => 'Excess Inventory',
            'inefficient_flow' => 'Inefficient Payment Flow',
            default => $type,
        };
    }
}
