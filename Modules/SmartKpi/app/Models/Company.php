<?php

namespace Modules\SmartKpi\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class Company extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'smartkpi_companies';

    protected $fillable = [
        'team_id', 'user_id', 'name', 'description', 'industry', 'size', 'timezone', 'logo', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function kpiDefinitions(): HasMany
    {
        return $this->hasMany(KpiDefinition::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
