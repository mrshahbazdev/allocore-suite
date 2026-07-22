<?php

namespace Modules\NurDu\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\NurDu\Models\Concerns\BelongsToCurrentTeam;

class QuarterlyFocus extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'nurdu_quarterly_focuses';

    protected $fillable = ['team_id', 'user_id', 'quarter', 'year', 'notes'];

    protected $casts = [
        'year' => 'integer',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function strategicPriorities(): HasMany
    {
        return $this->hasMany(StrategicPriority::class);
    }
}
