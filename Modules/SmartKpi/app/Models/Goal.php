<?php

namespace Modules\SmartKpi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class Goal extends Model
{
    protected $table = 'smartkpi_goals';

    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'company_id', 'department_id', 'kpi_definition_id',
        'name_en', 'name_de', 'target_value', 'current_value', 'progress', 'deadline', 'status',
    ];

    protected $casts = [
        'target_value' => 'float',
        'current_value' => 'float',
        'progress' => 'float',
        'deadline' => 'date',
    ];

    public function localizedName(): string
    {
        $locale = app()->getLocale();

        return $locale === 'de' ? ($this->name_de ?? $this->name_en) : $this->name_en;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    public function updateProgress(): void
    {
        $latest = $this->kpiDefinition?->latestValue?->value;
        $this->current_value = $latest !== null ? (float) $latest : ($this->current_value ?? 0);

        if ($this->target_value && $this->target_value > 0) {
            $this->progress = min(100, max(0, ($this->current_value / $this->target_value) * 100));
            $this->status = $this->progress >= 100 ? 'achieved' : 'active';
        }

        $this->saveQuietly();
    }
}
