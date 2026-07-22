<?php

namespace Modules\OrgMatrix\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\OrgMatrix\Models\Concerns\BelongsToCurrentTeam;

class RoleAssignment extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    public const HORIZONS = ['short', 'mid', 'long'];

    protected $table = 'orgmatrix_role_assignments';

    protected $fillable = [
        'team_id',
        'role_id',
        'person_id',
        'is_primary',
        'succession_horizon',
        'readiness_score',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'readiness_score' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
