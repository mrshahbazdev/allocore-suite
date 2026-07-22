<?php

namespace Modules\FocusMatrix\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FocusMatrix\Models\Concerns\BelongsToCurrentTeam;

class OrgCheck extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'focusmatrix_org_checks';

    protected $fillable = [
        'team_id',
        'user_id',
        'year',
        'week',
        'decides_what_clear',
        'responsibilities_clear',
        'reports_short',
        'teams_small',
        'notes',
        'health_score',
    ];

    protected $casts = [
        'decides_what_clear' => 'boolean',
        'responsibilities_clear' => 'boolean',
        'reports_short' => 'boolean',
        'teams_small' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
