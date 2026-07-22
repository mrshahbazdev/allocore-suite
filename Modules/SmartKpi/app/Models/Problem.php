<?php

namespace Modules\SmartKpi\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class Problem extends Model
{
    protected $table = 'smartkpi_problems';

    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'kpi_definition_id', 'company_id', 'department_id', 'detected_by',
        'title', 'description', 'severity', 'status', 'detected_at',
    ];

    protected $casts = [
        'detected_at' => 'date',
    ];

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function detector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'detected_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }
}
