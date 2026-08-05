<?php

namespace Modules\SopBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    protected $table = 'sop_checklist_items';

    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
        'is_required' => 'boolean',
    ];

    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class);
    }
}
