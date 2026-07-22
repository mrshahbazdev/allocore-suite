<?php

namespace Modules\SmartKpi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class AlertRule extends Model
{
    protected $table = 'smartkpi_alert_rules';

    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'kpi_definition_id', 'company_id', 'threshold_type', 'threshold_value', 'severity', 'is_active',
    ];

    protected $casts = [
        'threshold_value' => 'float',
        'is_active' => 'boolean',
    ];

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
