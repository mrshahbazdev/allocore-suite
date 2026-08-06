<?php

namespace Modules\SopBuilder\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidence extends Model
{
    protected $table = 'sop_evidence';

    protected $guarded = [];

    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
