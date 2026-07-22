<?php

namespace Modules\TimeButler\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\TimeButler\Models\Concerns\BelongsToCurrentTeam;

class VacationBalance extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'timebutler_vacation_balances';

    protected $fillable = [
        'team_id',
        'user_id',
        'year',
        'total_days',
        'taken_days',
        'requested_days',
        'remaining_days',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'total_days' => 'decimal:1',
            'taken_days' => 'decimal:1',
            'requested_days' => 'decimal:1',
            'remaining_days' => 'decimal:1',
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
