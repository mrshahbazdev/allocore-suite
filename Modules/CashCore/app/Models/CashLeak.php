<?php

namespace Modules\CashCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CashCore\Models\Concerns\BelongsToCurrentTeam;

class CashLeak extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'user_id', 'cashcore_transaction_id', 'leak_type', 'title',
        'description', 'monthly_amount', 'leak_score', 'status', 'recommendation',
    ];

    protected $casts = [
        'monthly_amount' => 'float',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class, 'cashcore_transaction_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['detected', 'reviewed']);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'rising_costs' => 'Rising Costs',
            'unused_subscription' => 'Unused Subscription',
            'duplicate_tool' => 'Duplicate Tool',
            'dead_expense' => 'Dead Expense',
            'no_function' => 'No Clear Function',
            default => $type,
        };
    }
}
