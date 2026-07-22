<?php

namespace Modules\PlanHive\Models\Concerns;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

trait BelongsToCurrentTeam
{
    protected static function bootBelongsToCurrentTeam(): void
    {
        static::addGlobalScope('current_team', function (Builder $builder): void {
            if (! auth()->check()) {
                return;
            }

            $teamId = auth()->user()?->current_team_id;

            $teamId
                ? $builder->where($builder->qualifyColumn('team_id'), $teamId)
                : $builder->whereRaw('1 = 0');
        });

        static::creating(function ($model): void {
            $model->team_id ??= auth()->user()?->current_team_id;

            if (Schema::hasColumn($model->getTable(), 'user_id')) {
                $model->user_id ??= auth()->id();
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
