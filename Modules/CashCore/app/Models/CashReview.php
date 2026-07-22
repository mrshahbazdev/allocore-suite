<?php

namespace Modules\CashCore\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\CashCore\Models\Concerns\BelongsToCurrentTeam;

class CashReview extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'user_id', 'review_type', 'scheduled_date', 'completed_date',
        'status', 'notes', 'checklist', 'streak_count',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'checklist' => 'array',
    ];

    protected $table = 'cashcore_reviews';

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public static function defaultChecklist(): array
    {
        return [
            ['task' => 'Review all new expenses', 'done' => false],
            ['task' => 'Check recurring subscriptions', 'done' => false],
            ['task' => 'Evaluate cost-to-revenue ratio', 'done' => false],
            ['task' => 'Review detected leaks', 'done' => false],
            ['task' => 'Update expense scores', 'done' => false],
            ['task' => 'Check open invoices', 'done' => false],
            ['task' => 'Verify profit allocation', 'done' => false],
        ];
    }
}
