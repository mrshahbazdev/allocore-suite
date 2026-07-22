<?php

namespace Modules\TimeButler\Models;

use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\TimeButler\Models\Concerns\BelongsToCurrentTeam;

class TimeEntry extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'timebutler_time_entries';

    protected $fillable = [
        'team_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'break_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'break_minutes' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function durationMinutes(): ?int
    {
        if (! $this->end_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return max(0, $end->diffInMinutes($start) - ($this->break_minutes ?? 0));
    }
}
