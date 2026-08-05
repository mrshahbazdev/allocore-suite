<?php

namespace Modules\SopBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Step extends Model
{
    protected $table = 'sop_steps';

    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'step_id')->orderBy('sort_order');
    }
}
