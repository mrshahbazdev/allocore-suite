<?php

namespace Modules\SmartKpi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class Forecast extends Model
{
    protected $table = 'smartkpi_forecasts';

    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'kpi_definition_id', 'forecasted_at', 'horizon', 'method', 'value', 'confidence_lower', 'confidence_upper',
    ];

    protected $casts = [
        'forecasted_at' => 'date',
        'value' => 'float',
        'confidence_lower' => 'float',
        'confidence_upper' => 'float',
    ];

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }
}
