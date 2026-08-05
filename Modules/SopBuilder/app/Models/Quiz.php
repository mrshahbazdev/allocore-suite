<?php

namespace Modules\SopBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    protected $table = 'sop_quizzes';

    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
        'options' => 'array',
    ];

    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class);
    }
}
