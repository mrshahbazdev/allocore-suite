<?php

namespace App\Services;

use App\Models\AllocoreScore;
use App\Models\MaturityDataSnapshot;
use App\Models\Team;

class MaturityDataSnapshotService
{
    public static function fromScore(AllocoreScore $score): MaturityDataSnapshot
    {
        return MaturityDataSnapshot::updateOrCreate(
            ['team_id' => $score->team_id],
            [
                'audit_id' => $score->audit_id,
                'allocore_score_id' => $score->id,
                'company_name' => $score->company_name ?: ($score->team->company_name ?? $score->team->name ?? null),
                'industry' => $score->industry,
                'industry_sub' => $score->industry_sub,
                'size' => $score->size,
                'company_age' => $score->company_age,
                'country' => $score->team->country ?? null,
                'revenue_range' => $score->team->revenue_range ?? null,
                'score' => $score->score,
                'maturity_level' => $score->maturity_level,
                'pillars' => $score->pillars,
                'calculated_at' => $score->calculated_at,
            ]
        );
    }

    public static function syncForTeam(Team $team): ?MaturityDataSnapshot
    {
        $score = AllocoreScoreService::latestForTeam($team->id);

        if (! $score) {
            return null;
        }

        return self::fromScore($score);
    }

    public static function refreshAll(): int
    {
        $count = 0;

        foreach (Team::cursor() as $team) {
            if (self::syncForTeam($team)) {
                $count++;
            }
        }

        return $count;
    }
}
