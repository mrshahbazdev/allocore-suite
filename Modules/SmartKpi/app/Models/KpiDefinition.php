<?php

namespace Modules\SmartKpi\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class KpiDefinition extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'user_id', 'company_id', 'department_id', 'responsible_user_id',
        'name_en', 'name_de', 'description_en', 'description_de', 'formula', 'unit',
        'target_value', 'warning_threshold', 'critical_threshold',
        'frequency', 'direction', 'category', 'is_template', 'is_active',
    ];

    protected $casts = [
        'target_value' => 'float',
        'warning_threshold' => 'float',
        'critical_threshold' => 'float',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function localizedName(): string
    {
        $locale = app()->getLocale();

        return $locale === 'de' ? ($this->name_de ?? $this->name_en) : $this->name_en;
    }

    public function localizedDescription(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'de' ? ($this->description_de ?? $this->description_en) : ($this->description_en ?? $this->description_de);
    }

    public function currentStatus(): ?string
    {
        $latest = $this->latestValue?->value;

        if ($latest === null) {
            return null;
        }

        return $this->statusForValue($latest);
    }

    public function statusForValue(float $value): string
    {
        if ($this->direction === 'asc') {
            if ($this->critical_threshold !== null && $value < $this->critical_threshold) {
                return 'critical';
            }
            if ($this->warning_threshold !== null && $value < $this->warning_threshold) {
                return 'warning';
            }
        } else {
            if ($this->critical_threshold !== null && $value > $this->critical_threshold) {
                return 'critical';
            }
            if ($this->warning_threshold !== null && $value > $this->warning_threshold) {
                return 'warning';
            }
        }

        return 'good';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(KpiValue::class)->orderBy('recorded_at', 'desc');
    }

    public function latestValue(): HasOne
    {
        return $this->hasOne(KpiValue::class)->latestOfMany('recorded_at');
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class);
    }

    public function alertRules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(Forecast::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'smartkpi_kpi_user', 'kpi_definition_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function causeRelationships(): HasMany
    {
        return $this->hasMany(KpiRelationship::class, 'cause_kpi_id');
    }

    public function effectRelationships(): HasMany
    {
        return $this->hasMany(KpiRelationship::class, 'effect_kpi_id');
    }
}
