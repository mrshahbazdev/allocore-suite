<?php

namespace Modules\BunnyBand\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class Notification extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_notifications';

    protected $fillable = [
        'team_id', 'bunnyband_profile_id', 'type', 'title', 'message', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BunnyBandProfile::class, 'bunnyband_profile_id');
    }
}
