<?php

namespace Modules\BunnyBand\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class Task extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_tasks';

    protected $fillable = [
        'team_id', 'title', 'description', 'type', 'reward', 'url',
        'verification_method', 'is_active', 'max_completions', 'cooldown_hours',
    ];

    protected $casts = [
        'reward' => 'float',
        'is_active' => 'boolean',
    ];

    public function userTasks(): HasMany
    {
        return $this->hasMany(UserTask::class, 'bunnyband_task_id');
    }
}
