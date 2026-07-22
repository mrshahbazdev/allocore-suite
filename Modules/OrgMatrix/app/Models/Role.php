<?php

namespace Modules\OrgMatrix\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\OrgMatrix\Models\Concerns\BelongsToCurrentTeam;

class Role extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    public const CRITICALITIES = ['low', 'medium', 'high', 'critical'];

    protected $table = 'orgmatrix_roles';

    protected $fillable = [
        'team_id',
        'organization_id',
        'parent_role_id',
        'name',
        'description',
        'department',
        'criticality',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_role_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_role_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'orgmatrix_role_assignments')
            ->withPivot('is_primary', 'succession_horizon', 'readiness_score', 'start_date', 'end_date', 'notes')
            ->withTimestamps();
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive', 'assignments.person');
    }
}
