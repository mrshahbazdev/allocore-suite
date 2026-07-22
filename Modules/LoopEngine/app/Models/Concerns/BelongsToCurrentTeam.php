<?php

namespace Modules\LoopEngine\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCurrentTeam
{
    protected static function bootBelongsToCurrentTeam(): void
    {
        static::addGlobalScope('current_team', function (Builder $builder): void {
            if (auth()->check() && auth()->user()?->current_team_id) {
                $builder->where('team_id', auth()->user()->current_team_id);
            } else {
                $builder->whereRaw('1=0');
            }
        });

        static::creating(function ($model): void {
            $user = auth()->user();
            if ($user && ! $model->team_id) {
                $model->team_id = $user->current_team_id;
                if (! $model->user_id && in_array('user_id', $model->getFillable(), true)) {
                    $model->user_id = $user->id;
                }
            }
        });
    }
}
