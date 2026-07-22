<?php

namespace Modules\KpiTool\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\KpiTool\Models\Concerns\BelongsToCurrentTeam;

class KpiValue extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'kpitool_kpi_values';

    protected $fillable = [
        'team_id',
        'kpi_definition_id',
        'recorded_by',
        'value',
        'recorded_at',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'recorded_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model): void {
            $model->team_id ??= auth()->user()?->current_team_id;
            $model->recorded_by ??= auth()->id();

            if ($model->kpiDefinition) {
                $model->status = $model->kpiDefinition->statusFor((float) $model->value);
            }
        });

        static::updating(function ($model): void {
            if ($model->isDirty('value') && $model->kpiDefinition) {
                $model->status = $model->kpiDefinition->statusFor((float) $model->value);
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
