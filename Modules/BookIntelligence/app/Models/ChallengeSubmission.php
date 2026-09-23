<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class ChallengeSubmission extends Model
{
    use BelongsToCurrentTeam;

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_GRADED = 'graded';
    public const STATUS_NEEDS_REVISION = 'needs_revision';

    protected $table = 'bookintelligence_challenge_submissions';

    protected $fillable = [
        'team_id',
        'challenge_id',
        'user_id',
        'submission_text',
        'submission_attachment_url',
        'reflection_answers',
        'status',
        'grade_score',
        'evaluator_feedback',
    ];

    protected $casts = [
        'reflection_answers' => 'array',
        'grade_score' => 'decimal:2',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(PracticalChallenge::class, 'challenge_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
