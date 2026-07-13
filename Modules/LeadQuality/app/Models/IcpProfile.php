<?php

namespace Modules\LeadQuality\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\LeadQuality\Models\Concerns\BelongsToCurrentTeam;

class IcpProfile extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'leadquality_icp_profiles';

    protected $fillable = [
        'team_id',
        'user_id',
        'industry',
        'employee_count_range',
        'budget_min',
        'budget_max',
        'role',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
        ];
    }
}
