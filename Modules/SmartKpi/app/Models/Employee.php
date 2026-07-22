<?php

namespace Modules\SmartKpi\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class Employee extends Model
{
    protected $table = 'smartkpi_employees';

    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'company_id', 'department_id', 'user_id', 'name', 'email', 'role', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
