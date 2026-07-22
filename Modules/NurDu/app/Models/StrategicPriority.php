<?php

namespace Modules\NurDu\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\NurDu\Models\Concerns\BelongsToCurrentTeam;

class StrategicPriority extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'nurdu_strategic_priorities';

    protected $fillable = ['quarterly_focus_id', 'title', 'owner', 'kpi', 'status', 'notes'];

    public function quarterlyFocus(): BelongsTo
    {
        return $this->belongsTo(QuarterlyFocus::class);
    }
}
