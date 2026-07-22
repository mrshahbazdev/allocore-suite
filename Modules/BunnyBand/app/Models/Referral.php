<?php

namespace Modules\BunnyBand\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class Referral extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_referrals';

    protected $fillable = [
        'team_id', 'referrer_id', 'referred_id', 'reward_amount', 'is_rewarded',
    ];

    protected $casts = [
        'reward_amount' => 'float',
        'is_rewarded' => 'boolean',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(BunnyBandProfile::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(BunnyBandProfile::class, 'referred_id');
    }
}
