<?php

namespace Modules\LeadQuality\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\LeadQuality\Models\Concerns\BelongsToCurrentTeam;

class OutreachTemplate extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'leadquality_outreach_templates';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'type',
        'content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
