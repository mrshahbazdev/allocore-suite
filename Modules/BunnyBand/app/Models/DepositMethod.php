<?php

namespace Modules\BunnyBand\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class DepositMethod extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_deposit_methods';

    protected $fillable = [
        'team_id', 'name', 'icon', 'icon_image', 'bank_name',
        'account_name', 'account_number', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
