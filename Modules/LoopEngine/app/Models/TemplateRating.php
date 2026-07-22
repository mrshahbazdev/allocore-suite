<?php

namespace Modules\LoopEngine\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\LoopEngine\Models\Concerns\BelongsToCurrentTeam;

class TemplateRating extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'loopengine_template_ratings';

    protected $fillable = [
        'team_id',
        'template_id',
        'user_id',
        'rating',
        'review',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProcessTemplate::class, 'template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
