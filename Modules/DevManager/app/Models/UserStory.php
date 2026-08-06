<?php

namespace Modules\DevManager\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\DevManager\Models\Concerns\BelongsToCurrentTeam;

class UserStory extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'devmanager_user_stories';

    protected $guarded = [];

    protected $casts = [
        'story_points' => 'integer',
        'metadata' => 'array',
    ];

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class, 'requirement_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
