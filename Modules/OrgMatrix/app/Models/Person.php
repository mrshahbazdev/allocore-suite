<?php

namespace Modules\OrgMatrix\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\OrgMatrix\Models\Concerns\BelongsToCurrentTeam;

class Person extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'orgmatrix_people';

    protected $fillable = [
        'team_id',
        'organization_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'title',
        'department',
        'avatar',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'orgmatrix_role_assignments')
            ->withPivot('is_primary', 'succession_horizon', 'readiness_score', 'start_date', 'end_date', 'notes')
            ->withTimestamps();
    }
}
