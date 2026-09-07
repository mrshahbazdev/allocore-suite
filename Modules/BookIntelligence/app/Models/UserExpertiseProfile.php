<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class UserExpertiseProfile extends Model
{
    use BelongsToCurrentTeam;

    public const LEVELS = ['starter', 'junior', 'professional', 'senior', 'expert', 'master'];

    protected $table = 'bookintelligence_user_expertise_profiles';

    protected $fillable = [
        'team_id',
        'user_id',
        'current_role_id',
        'target_role_id',
        'expertise_level',
        'points',
        'books_read_count',
        'assessments_passed_count',
        'challenges_completed_count',
        'badges',
    ];

    protected $casts = [
        'points' => 'integer',
        'books_read_count' => 'integer',
        'assessments_passed_count' => 'integer',
        'challenges_completed_count' => 'integer',
        'badges' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentRole(): BelongsTo
    {
        return $this->belongsTo(CompetencyRole::class, 'current_role_id');
    }

    public function targetRole(): BelongsTo
    {
        return $this->belongsTo(CompetencyRole::class, 'target_role_id');
    }

    public function recalculateLevel(): void
    {
        $pts = $this->points;

        $newLevel = match (true) {
            $pts >= 5000 => 'master',
            $pts >= 3000 => 'expert',
            $pts >= 1500 => 'senior',
            $pts >= 750 => 'professional',
            $pts >= 250 => 'junior',
            default => 'starter',
        };

        $this->update(['expertise_level' => $newLevel]);
    }
}
