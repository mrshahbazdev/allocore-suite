<?php

namespace Modules\DevManager\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCurrentTeam
{
    protected static function bootBelongsToCurrentTeam(): void
    {
        static::addGlobalScope('current_team', function (Builder $builder): void {
            if (! auth()->check()) {
                return;
            }

            $teamId = auth()->user()->current_team_id;

            $teamId
                ? $builder->where($builder->qualifyColumn('team_id'), $teamId)
                : $builder->whereRaw('1 = 0');
        });

        static::creating(function ($model): void {
            $model->team_id ??= auth()->user()?->current_team_id;
            $model->user_id ??= auth()->user()?->id;
        });
    }
}
