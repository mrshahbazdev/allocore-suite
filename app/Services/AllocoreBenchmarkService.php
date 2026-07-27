<?php

namespace App\Services;

use App\Models\AllocoreScore;
use Illuminate\Database\Eloquent\Builder;

class AllocoreBenchmarkService
{
    public static function percentile(AllocoreScore $score): ?float
    {
        $scope = self::scopeQuery($score);

        if ($scope === null) {
            return null;
        }

        $total = (clone $scope)
            ->whereNotNull('score')
            ->where('id', '!=', $score->id)
            ->count();

        if ($total === 0) {
            return null;
        }

        $below = (clone $scope)
            ->where('score', '<', $score->score)
            ->where('id', '!=', $score->id)
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

    private static function scopeQuery(AllocoreScore $score): ?Builder
    {
        if ($score->industry === null) {
            return null;
        }

        $subQuery = self::queryFor($score->industry, $score->industry_sub);

        if ($subQuery->whereNotNull('score')->where('id', '!=', $score->id)->count() >= 5) {
            return $subQuery;
        }

        return self::queryFor($score->industry, null);
    }

    private static function queryFor(string $industry, ?string $subIndustry = null): Builder
    {
        $query = AllocoreScore::where('industry', $industry);

        if ($subIndustry) {
            $query->where('industry_sub', $subIndustry);
        }

        return $query;
    }
}
