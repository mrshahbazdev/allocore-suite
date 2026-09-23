<?php

namespace Modules\BookIntelligence\Services;

use App\Models\User;
use Modules\BookIntelligence\Models\UserExpertiseProfile;

class ExpertiseProgressionService
{
    /**
     * Get or initialize a user's expertise profile.
     */
    public function getProfile(User $user): UserExpertiseProfile
    {
        $teamId = $user->currentTeam?->id ?? 1;

        return UserExpertiseProfile::firstOrCreate(
            ['team_id' => $teamId, 'user_id' => $user->id],
            [
                'expertise_level' => 'starter',
                'points' => 0,
                'books_read_count' => 0,
                'assessments_passed_count' => 0,
                'challenges_completed_count' => 0,
                'badges' => [
                    ['name' => 'Knowledge Pioneer', 'awarded_at' => now()->toDateString(), 'icon' => '🌟'],
                ],
            ]
        );
    }

    public function recordBookRead(User $user, int $points = 50): void
    {
        $profile = $this->getProfile($user);
        $profile->increment('books_read_count');
        $profile->increment('points', $points);
        $profile->recalculateLevel();
    }

    public function recordAssessmentPassed(User $user, int $points = 100): void
    {
        $profile = $this->getProfile($user);
        $profile->increment('assessments_passed_count');
        $profile->increment('points', $points);
        $profile->recalculateLevel();
    }

    public function recordChallengeCompleted(User $user, int $points = 200): void
    {
        $profile = $this->getProfile($user);
        $profile->increment('challenges_completed_count');
        $profile->increment('points', $points);
        $profile->recalculateLevel();
    }
}
