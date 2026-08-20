<?php

namespace App\Services;

use App\Models\AllocoreScore;
use App\Models\MaturityDataSnapshot;
use Illuminate\Database\Eloquent\Builder;

class AllocoreBenchmarkService
{
    public static function percentile(AllocoreScore $score): ?float
    {
        if (! self::snapshotsActive()) {
            return self::percentileFromAllocoreScore($score);
        }

        $snapshot = MaturityDataSnapshotService::fromScore($score);
        $scope = self::scopeQuery($snapshot);

        if ($scope === null) {
            return null;
        }

        $total = (clone $scope)
            ->where('team_id', '!=', $snapshot->team_id)
            ->whereNotNull('score')
            ->count();

        if ($total === 0) {
            return null;
        }

        $below = (clone $scope)
            ->where('team_id', '!=', $snapshot->team_id)
            ->where('score', '<', $snapshot->score)
            ->count();

        return round(($below / $total) * 100, 1);
    }

    public static function industryAverage(string $industry, ?string $subIndustry = null): ?float
    {
        $avg = self::queryFor($industry, $subIndustry)
            ->whereNotNull('score')
            ->avg('score');

        return $avg === null ? null : round((float) $avg, 1);
    }

    public static function industryStats(string $industry, ?string $subIndustry = null): array
    {
        $scores = self::queryFor($industry, $subIndustry)
            ->whereNotNull('score')
            ->pluck('score')
            ->map(fn ($s) => (float) $s)
            ->sort()
            ->values();

        $count = $scores->count();

        return [
            'count' => $count,
            'average' => $count ? round($scores->avg(), 1) : null,
            'median' => $count ? round($scores->median(), 1) : null,
            'min' => $count ? round($scores->first(), 1) : null,
            'max' => $count ? round($scores->last(), 1) : null,
        ];
    }

    private static function snapshotsActive(): bool
    {
        return MaturityDataSnapshot::query()->exists();
    }

    private static function scopeQuery(MaturityDataSnapshot $snapshot): ?Builder
    {
        if ($snapshot->industry === null) {
            return null;
        }

        $subQuery = self::queryFor($snapshot->industry, $snapshot->industry_sub);

        if ($subQuery->whereNotNull('score')->where('team_id', '!=', $snapshot->team_id)->count() >= 5) {
            return $subQuery;
        }

        return self::queryFor($snapshot->industry, null);
    }

    private static function queryFor(string $industry, ?string $subIndustry = null): Builder
    {
        $query = self::snapshotsActive()
            ? MaturityDataSnapshot::query()
            : AllocoreScore::query();

        $query->where('industry', $industry);

        if ($subIndustry) {
            $query->where('industry_sub', $subIndustry);
        }

        return $query;
    }

    private static function percentileFromAllocoreScore(AllocoreScore $score): ?float
    {
        if ($score->industry === null) {
            return null;
        }

        $scope = self::queryFor($score->industry, $score->industry_sub);

        if ($scope->where('id', '!=', $score->id)->whereNotNull('score')->count() < 5) {
            $scope = self::queryFor($score->industry, null);
        }

        $total = (clone $scope)
            ->where('id', '!=', $score->id)
            ->whereNotNull('score')
            ->count();

        if ($total === 0) {
            return null;
        }

        $below = (clone $scope)
            ->where('id', '!=', $score->id)
            ->where('score', '<', $score->score)
            ->count();

        return round(($below / $total) * 100, 1);
    }
}
