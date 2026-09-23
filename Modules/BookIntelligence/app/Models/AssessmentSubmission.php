<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class AssessmentSubmission extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_assessment_submissions';

    protected $fillable = [
        'team_id',
        'assessment_id',
        'user_id',
        'answers',
        'score',
        'passed',
        'strengths',
        'gaps',
        'feedback',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
        'passed' => 'boolean',
        'strengths' => 'array',
        'gaps' => 'array',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(SkillAssessment::class, 'assessment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
