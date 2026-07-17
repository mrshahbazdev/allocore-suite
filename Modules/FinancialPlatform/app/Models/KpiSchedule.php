<?php

namespace Modules\FinancialPlatform\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class KpiSchedule extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'financial_kpi_schedules';

    protected $fillable = [
        'team_id',
        'user_id',
        'frequency',
        'recipients',
        'is_active',
        'last_run_at',
        'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'is_active' => 'boolean',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculateNextRun(): Carbon
    {
        $date = $this->last_run_at ?? now();

        return match ($this->frequency) {
            'daily' => $date->copy()->addDay(),
            'weekly' => $date->copy()->addWeek(),
            'monthly' => $date->copy()->addMonth(),
            default => $date->copy()->addMonth(),
        };
    }
}
