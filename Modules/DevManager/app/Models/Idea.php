<?php

namespace Modules\DevManager\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\DevManager\Models\Concerns\BelongsToCurrentTeam;

class Idea extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'devmanager_ideas';

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class, 'idea_id');
    }

    public function userStories(): HasMany
    {
        return $this->hasMany(UserStory::class, 'idea_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'idea_id');
    }

    public function releases(): HasMany
    {
        return $this->hasMany(Release::class, 'idea_id');
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class, 'idea_id');
    }

    public function scopeStatusCounts(): array
    {
        return [
            'requirements' => $this->requirements()->count(),
            'done_requirements' => $this->requirements()->where('status', 'done')->count(),
            'stories' => $this->userStories()->count(),
            'done_stories' => $this->userStories()->where('status', 'done')->count(),
            'milestones' => $this->milestones()->count(),
            'releases' => $this->releases()->count(),
        ];
    }
}
