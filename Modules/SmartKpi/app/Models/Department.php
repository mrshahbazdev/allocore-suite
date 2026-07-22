<?php

namespace Modules\SmartKpi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class Department extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'company_id', 'parent_id', 'name', 'description', 'industry_type', 'size', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function kpiDefinitions(): HasMany
    {
        return $this->hasMany(KpiDefinition::class);
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class);
    }
}
