<?php

namespace Modules\NurDu\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\NurDu\Models\Concerns\BelongsToCurrentTeam;

class ActionItem extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'nurdu_action_items';

    protected $fillable = ['vision_check_id', 'title', 'completed'];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function visionCheck(): BelongsTo
    {
        return $this->belongsTo(VisionCheck::class);
    }
}
