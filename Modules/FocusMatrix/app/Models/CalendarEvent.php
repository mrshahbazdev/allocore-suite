<?php

namespace Modules\FocusMatrix\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FocusMatrix\Models\Concerns\BelongsToCurrentTeam;

class CalendarEvent extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    public const COLORS = ['accent', 'navy', 'emerald', 'amber', 'rose', 'graphite'];

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_FOCUS_BLOCK = 'focus_block';

    public const SOURCE_TASK_DUE = 'task_due';

    protected $table = 'focusmatrix_calendar_events';

    protected $fillable = [
        'team_id',
        'user_id',
        'title',
        'description',
        'location',
        'color',
        'all_day',
        'starts_at',
        'ends_at',
        'source',
        'task_id',
    ];

    protected $casts = [
        'all_day' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
