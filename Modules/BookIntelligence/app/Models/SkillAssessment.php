<?php

namespace Modules\BookIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class SkillAssessment extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_skill_assessments';

    protected $fillable = [
        'team_id',
        'book_id',
        'role_id',
        'title',
        'description',
        'questions',
        'passing_score',
        'time_limit_minutes',
    ];

    protected $casts = [
        'questions' => 'array',
        'passing_score' => 'integer',
        'time_limit_minutes' => 'integer',
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
        return $this->hasMany(AssessmentSubmission::class, 'assessment_id');
    }
}
