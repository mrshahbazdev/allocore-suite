<?php

namespace App\Services;

use App\Models\AllocoreScore;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditResult;
use Modules\AuditPro\Support\Maturity;

class AllocoreScoreService
{
    public static function fromAudit(Audit $audit): AllocoreScore
    {
        $results = AuditResult::where('audit_id', $audit->id)->get();

        $pillars = $results->map(function (AuditResult $result) {
            $raw = (float) $result->average_score;

            return [
                'name' => $result->level,
                'score' => self::to100($raw),
                'maturity' => $result->maturity_level ?: Maturity::label($raw),
            ];
        })->values()->all();

        $averageRaw = $results->avg('average_score') ?: 0;
        $score100 = self::to100($averageRaw);

        return AllocoreScore::create([
            'team_id' => $audit->team_id,
            'audit_id' => $audit->id,
            'company_name' => $audit->company_name ?: ($audit->team->company_name ?: $audit->team->name),
            'industry' => $audit->industry ?: $audit->team->industry,
            'size' => $audit->size ?: $audit->team->size,
            'company_age' => $audit->company_age ?? $audit->team->company_age,
            'score' => $score100,
            'maturity_level' => Maturity::label($averageRaw),
            'pillars' => $pillars,
            'calculated_at' => now(),
        ]);
    }

    public static function latestForTeam(?int $teamId): ?AllocoreScore
    {
        if ($teamId === null) {
            return null;
        }

        return AllocoreScore::where('team_id', $teamId)->latest('calculated_at')->first();
    }

    public static function historyForTeam(?int $teamId, int $limit = 12): array
    {
        if ($teamId === null) {
            return [];
        }

        return AllocoreScore::where('team_id', $teamId)
            ->oldest('calculated_at')
            ->take($limit)
            ->get()
            ->map(fn (AllocoreScore $s) => [
                'date' => $s->calculated_at->format('Y-m-d'),
                'score' => (float) $s->score,
                'maturity' => $s->maturity_level,
            ])
            ->values()
            ->all();
    }

    private static function to100(float $raw): float
    {
        return round(($raw / 5) * 100, 2);
    }
}
