<?php

namespace Modules\BunnyBand\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class UserTask extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_user_tasks';

    protected $fillable = [
        'team_id', 'bunnyband_profile_id', 'bunnyband_task_id', 'status',
        'proof', 'completed_at', 'verified_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BunnyBandProfile::class, 'bunnyband_profile_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'bunnyband_task_id');
    }
}
