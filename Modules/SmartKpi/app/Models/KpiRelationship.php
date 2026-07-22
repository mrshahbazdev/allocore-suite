<?php

namespace Modules\SmartKpi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class KpiRelationship extends Model
{
    protected $table = 'smartkpi_kpi_relationships';

    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'cause_kpi_id', 'effect_kpi_id', 'lag_periods', 'correlation', 'is_active',
    ];

    protected $casts = [
        'lag_periods' => 'integer',
        'correlation' => 'float',
        'is_active' => 'boolean',
    ];

    public function causeKpi(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class, 'cause_kpi_id');
    }

    public function effectKpi(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class, 'effect_kpi_id');
    }
}
