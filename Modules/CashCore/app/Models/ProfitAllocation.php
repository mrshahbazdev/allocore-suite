<?php

namespace Modules\CashCore\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\CashCore\Models\Concerns\BelongsToCurrentTeam;

class ProfitAllocation extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'user_id', 'bucket', 'percentage', 'allocated_amount', 'period', 'notes',
    ];

    protected $casts = [
        'percentage' => 'float',
        'allocated_amount' => 'float',
    ];

    protected $table = 'cashcore_profit_allocations';

    public static function defaultBuckets(): array
    {
        return [
            ['bucket' => 'profit', 'percentage' => 10],
            ['bucket' => 'taxes', 'percentage' => 15],
            ['bucket' => 'salary', 'percentage' => 50],
            ['bucket' => 'operations', 'percentage' => 25],
        ];
    }

    public static function initializeForTeam(int $teamId, ?int $userId, string $period): void
    {
        foreach (self::defaultBuckets() as $bucket) {
            self::firstOrCreate(
                ['team_id' => $teamId, 'bucket' => $bucket['bucket'], 'period' => $period],
                array_merge($bucket, ['team_id' => $teamId, 'user_id' => $userId, 'period' => $period, 'allocated_amount' => 0])
            );
        }
    }
}
