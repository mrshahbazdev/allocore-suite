<?php

namespace Modules\SmartKpi\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class KpiValue extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'kpi_definition_id', 'recorded_by', 'value', 'recorded_at', 'status', 'notes',
    ];

    protected $casts = [
        'value' => 'float',
        'recorded_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function ($value): void {
            if ($value->kpiDefinition) {
                $value->status = $value->kpiDefinition->statusForValue((float) $value->value);
            }
        });
    }

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
