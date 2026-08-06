<?php

namespace Modules\DevManager\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\DevManager\Models\Concerns\BelongsToCurrentTeam;

class Requirement extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'devmanager_requirements';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userStories(): HasMany
    {
        return $this->hasMany(UserStory::class, 'requirement_id');
    }
}
