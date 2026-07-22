<?php

namespace Modules\FocusMatrix\Models\Concerns;

trait BelongsToCurrentTeam
{
    protected static function bootBelongsToCurrentTeam(): void
    {
        static::creating(function ($model): void {
            $user = auth()->user();
            if (! $model->team_id && $user?->current_team_id) {
                $model->team_id = $user->current_team_id;
            }
        });

        static::addGlobalScope('currentTeam', function ($builder) {
            $user = auth()->user();
            if ($user?->current_team_id) {
                $builder->where($builder->getModel()->getTable().'.team_id', $user->current_team_id);
            }
        });
    }
}
