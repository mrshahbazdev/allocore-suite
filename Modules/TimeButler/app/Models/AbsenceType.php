<?php

namespace Modules\TimeButler\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\TimeButler\Models\Concerns\BelongsToCurrentTeam;

class AbsenceType extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'timebutler_absence_types';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'color',
        'requires_approval',
        'is_paid',
        'deducts_vacation',
        'max_days_per_year',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'is_paid' => 'boolean',
            'deducts_vacation' => 'boolean',
            'is_active' => 'boolean',
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

    public function absenceRequests(): HasMany
    {
        return $this->hasMany(AbsenceRequest::class);
    }
}
