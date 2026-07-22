<?php

namespace Modules\PlanHive\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\PlanHive\Models\Concerns\BelongsToCurrentTeam;

class Reminder extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'planhive_reminders';

    protected $fillable = [
        'team_id',
        'project_id',
        'user_id',
        'remindable_type',
        'remindable_id',
        'title',
        'remind_at',
        'is_done',
    ];

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'is_done' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }
}
