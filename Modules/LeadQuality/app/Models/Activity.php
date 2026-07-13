<?php

namespace Modules\LeadQuality\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\LeadQuality\Models\Concerns\BelongsToCurrentTeam;

class Activity extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'leadquality_activities';

    protected $fillable = [
        'team_id',
        'user_id',
        'contact_id',
        'type',
        'scheduled_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
