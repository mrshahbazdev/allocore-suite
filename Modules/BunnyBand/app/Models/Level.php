<?php

namespace Modules\BunnyBand\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class Level extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_levels';

    protected $fillable = [
        'team_id', 'name', 'description', 'icon', 'icon_image', 'type',
        'price', 'daily_earning_limit', 'referral_bonus', 'task_bonus_percent',
        'withdrawal_limit', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'daily_earning_limit' => 'float',
        'referral_bonus' => 'float',
        'task_bonus_percent' => 'float',
        'withdrawal_limit' => 'float',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(BunnyBandProfile::class, 'level_id');
    }
}
