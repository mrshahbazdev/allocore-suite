<?php

namespace App\Services;

use App\Models\AllocoreScore;

class AllocoreBenchmarkService
{
    public static function percentile(AllocoreScore $score): ?float
    {
        if ($score->industry === null) {
            return null;
        }

        $total = AllocoreScore::where('industry', $score->industry)
            ->whereNotNull('score')
            ->where('id', '!=', $score->id)
            ->count();

        if ($total === 0) {
            return null;
        }

        $below = AllocoreScore::where('industry', $score->industry)
            ->where('score', '<', $score->score)
            ->where('id', '!=', $score->id)
            ->count();

        return round(($below / $total) * 100, 1);
    }

    public static function industryAverage(string $industry): ?float
    {
        $avg = AllocoreScore::where('industry', $industry)
            ->whereNotNull('score')
            ->avg('score');

        return $avg === null ? null : round((float) $avg, 1);
    }

    public static function industryStats(string $industry): array
    {
        $scores = AllocoreScore::where('industry', $industry)
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
}
