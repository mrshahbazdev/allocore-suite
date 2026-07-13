<?php

namespace Modules\LeadQuality\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequenceStep extends Model
{
    protected $table = 'leadquality_sequence_steps';

    protected $fillable = [
        'team_id',
        'sequence_id',
        'order',
        'delay_days',
        'subject',
        'body',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }
}
