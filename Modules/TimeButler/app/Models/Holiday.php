<?php

namespace Modules\TimeButler\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\TimeButler\Models\Concerns\BelongsToCurrentTeam;

class Holiday extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'timebutler_holidays';

    protected $fillable = [
        'team_id',
        'user_id',
        'date',
        'name',
        'type',
        'federal_state',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'year' => 'integer',
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
}
