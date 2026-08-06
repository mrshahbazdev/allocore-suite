<?php

namespace Modules\SopBuilder\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Completion extends Model
{
    protected $table = 'sop_completions';

    protected $guarded = [];

    protected $casts = [
        'completed_at' => 'datetime',
        'answers' => 'array',
        'checked_items' => 'array',
        'score' => 'integer',
    ];

    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
