<?php

namespace Modules\BunnyBand\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class WithdrawalMethod extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_withdrawal_methods';

    protected $fillable = [
        'team_id', 'name', 'fields', 'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];
}
