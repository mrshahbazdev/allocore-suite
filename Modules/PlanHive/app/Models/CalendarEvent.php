<?php

namespace Modules\PlanHive\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\PlanHive\Models\Concerns\BelongsToCurrentTeam;

class CalendarEvent extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'planhive_calendar_events';

    protected $fillable = [
        'team_id',
        'project_id',
        'user_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'all_day',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'all_day' => 'boolean',
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
}
