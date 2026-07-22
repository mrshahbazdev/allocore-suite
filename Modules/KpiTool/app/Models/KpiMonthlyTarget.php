<?php

namespace Modules\KpiTool\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\KpiTool\Models\Concerns\BelongsToCurrentTeam;

class KpiMonthlyTarget extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'kpitool_kpi_monthly_targets';

    protected $fillable = [
        'team_id',
        'kpi_definition_id',
        'year',
        'month',
        'target_value',
        'growth_rate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'target_value' => 'decimal:4',
            'growth_rate' => 'decimal:4',
        ];
    }

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }
}
