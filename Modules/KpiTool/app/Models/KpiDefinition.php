<?php

namespace Modules\KpiTool\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KpiDefinition extends Model
{
    use HasFactory;

    protected $table = 'kpitool_kpi_definitions';

    protected $fillable = [
        'team_id',
        'user_id',
        'name_de',
        'name_en',
        'description_de',
        'description_en',
        'formula',
        'unit',
        'target_value',
        'warning_threshold',
        'critical_threshold',
        'frequency',
        'direction',
        'category',
        'is_template',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:4',
            'warning_threshold' => 'decimal:4',
            'critical_threshold' => 'decimal:4',
            'is_template' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current_team_or_template', function (Builder $builder): void {
            if (! auth()->check()) {
                return;
            }

            $teamId = auth()->user()?->current_team_id;

            $builder->where(function (Builder $query) use ($teamId): void {
                $query->where('team_id', $teamId)
                    ->orWhere(function (Builder $templateQuery): void {
                        $templateQuery->whereNull('team_id')->where('is_template', true);
                    });
            });
        });

        static::creating(function ($model): void {
            if (! $model->is_template) {
                $model->team_id ??= auth()->user()?->current_team_id;
                $model->user_id ??= auth()->id();
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(KpiValue::class);
    }

    public function latestValue(): HasOne
    {
        return $this->hasOne(KpiValue::class)->latestOfMany('recorded_at');
    }

    public function monthlyTargets(): HasMany
    {
        return $this->hasMany(KpiMonthlyTarget::class);
    }

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();

        return $locale === 'de' ? $this->name_de : $this->name_en;
    }

    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'de' ? $this->description_de : $this->description_en;
    }

    public function statusFor(float $value): string
    {
        if ($this->warning_threshold === null || $this->critical_threshold === null) {
            return 'on_target';
        }

        if ($this->direction === 'higher_better') {
            if ($value < $this->critical_threshold) {
                return 'critical';
            }
            if ($value < $this->warning_threshold) {
                return 'warning';
            }
        } else {
            if ($value > $this->critical_threshold) {
                return 'critical';
            }
            if ($value > $this->warning_threshold) {
                return 'warning';
            }
        }

        return 'on_target';
    }

    public function duplicateForTeam(int $teamId, int $userId): self
    {
        $clone = $this->replicate();
        $clone->team_id = $teamId;
        $clone->user_id = $userId;
        $clone->is_template = false;
        $clone->save();

        return $clone;
    }
}
