<?php

namespace Modules\NurDu\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\NurDu\Models\Concerns\BelongsToCurrentTeam;

class GuidingPrinciple extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'nurdu_guiding_principles';

    protected $fillable = ['vision_id', 'title', 'description', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function vision(): BelongsTo
    {
        return $this->belongsTo(Vision::class);
    }
}
