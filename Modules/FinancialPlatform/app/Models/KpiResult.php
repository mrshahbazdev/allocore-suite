<?php

namespace Modules\FinancialPlatform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class KpiResult extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'financial_kpi_results';

    protected $fillable = [
        'team_id', 'analysis_id', 'kpi_code', 'kpi_name',
        'value', 'score', 'weight', 'traffic_light', 'unit', 'year_label',
    ];

    protected $casts = [
        'value' => 'decimal:6',
        'score' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }

    public function trafficLightEmoji(): string
    {
        return match ($this->traffic_light) {
            'green' => '🟢',
            'yellow' => '🟡',
            'red' => '🔴',
            default => '⚪',
        };
    }
}
