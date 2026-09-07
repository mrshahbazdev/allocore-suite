<?php

namespace Modules\BookIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class PracticalChallenge extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_practical_challenges';

    protected $fillable = [
        'team_id',
        'book_id',
        'role_id',
        'title',
        'scenario_description',
        'assignment_brief',
        'deliverable_format',
        'reflection_questions',
        'evaluation_rubric',
        'difficulty',
    ];

    protected $casts = [
        'reflection_questions' => 'array',
        'evaluation_rubric' => 'array',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(CompetencyRole::class, 'role_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ChallengeSubmission::class, 'challenge_id');
    }
}
