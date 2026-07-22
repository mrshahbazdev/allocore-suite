<?php

namespace Modules\BunnyBand\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class PaymentMethod extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_payment_methods';

    protected $fillable = [
        'team_id', 'bunnyband_profile_id', 'type', 'method',
        'account_name', 'account_number', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BunnyBandProfile::class, 'bunnyband_profile_id');
    }
}
