<?php

namespace Modules\KnowledgeManager\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\KnowledgeManager\Models\Concerns\BelongsToCurrentTeam;

class Project extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'knowledge_projects';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'project_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'project_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function progress(): int
    {
        $questions = collect(config('knowledgemanager.sections'))->pluck('questions')->flatten(1)->count();

        if ($questions === 0) {
            return 0;
        }

        $answered = $this->answers()->whereNotNull('answer')->where('answer', '!=', '')->count();

        return (int) round(($answered / $questions) * 100);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
