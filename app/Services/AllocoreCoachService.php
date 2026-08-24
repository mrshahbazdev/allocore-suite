<?php

namespace App\Services;

use App\Models\AllocoreScore;
use App\Models\GlossaryTerm;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AllocoreCoachService
{
    public function __construct(
        private AllocoreRecommendationService $recommendationService,
        private GlossaryService $glossaryService,
    ) {}

    public function forScore(?AllocoreScore $score, User $user): array
    {
        if (! $score) {
            return [
                'has_score' => false,
                'cta' => __('Start an audit to get your personal Allocore Coach.'),
            ];
        }

        $recommendations = $this->recommendationService->forScore($score, $user);
        $trend = $this->trend($score, $user);
        $strongest = $this->strongestPillar($score);
        $focus = $recommendations['items'][0] ?? null;

        return [
            'has_score' => true,
            'trend' => $trend,
            'benchmark' => $this->benchmark($score),
            'positive' => $this->positive($score, $strongest),
            'problem' => $this->problem($focus),
            'tool' => $this->tool($focus),
            'knowledge' => $this->knowledge($focus),
            'history' => $recommendations['history'] ?? null,
            'all' => array_map(fn (array $item) => $this->buildImprovement($score, $item), $recommendations['items'] ?? []),
        ];
    }

    protected function trend(AllocoreScore $score, User $user): array
    {
        $history = AllocoreScoreService::historyForTeam($user->current_team_id, 12);

        if (count($history) < 2) {
            return [
                'direction' => 'same',
                'delta' => 0,
                'previous' => null,
                'text' => __('first score'),
            ];
        }

        $lastTwo = array_slice($history, -2);
        $previous = (float) ($lastTwo[0]['score'] ?? 0);
        $current = (float) ($lastTwo[1]['score'] ?? 0);
        $delta = round($current - $previous, 1);

        return [
            'direction' => $delta > 0.01 ? 'up' : ($delta < -0.01 ? 'down' : 'same'),
            'delta' => abs($delta),
            'previous' => $previous,
            'text' => __('since last audit'),
        ];
    }

    protected function positive(AllocoreScore $score, ?array $strongest): array
    {
        $maturity = __($score->maturity_level);

        if ($strongest) {
            return [
                'headline' => __(':maturity — your :pillar is strongest right now.', [
                    'maturity' => $maturity,
                    'pillar' => __($strongest['name']),
                ]),
                'detail' => __('Your :pillar score is :score out of 100. Keep investing here while you work on the biggest gap.', [
                    'pillar' => __($strongest['name']),
                    'score' => $strongest['score'],
                ]),
            ];
        }

        return [
            'headline' => __('Your Allocore Score is :score — :maturity.', ['score' => $score->score, 'maturity' => $maturity]),
            'detail' => __('Complete the next small audit to get a more precise recommendation.'),
        ];
    }

    protected function problem(?array $focus): ?array
    {
        if (! $focus) {
            return null;
        }

        return [
            'pillar' => $focus['pillar'],
            'score' => $focus['score'],
            'headline' => __('Your biggest current gap is :pillar.', ['pillar' => __($focus['pillar'])]),
            'solution' => $this->glossaryService->linkTerms(__($focus['action'])),
        ];
    }

    protected function tool(?array $focus): ?array
    {
        if (! $focus || ! $focus['module_name']) {
            return null;
        }

        $module = Module::byKey($focus['module_key']);

        return [
            'name' => $focus['module_name'],
            'key' => $focus['module_key'],
            'route' => $focus['module_route'],
            'subscribed' => $focus['subscribed'],
            'guide' => $this->toolGuide($focus['module_key'], $focus['pillar'], $module?->description),
        ];
    }

    protected function toolGuide(string $moduleKey, string $pillar, ?string $description): string
    {
        $fallback = $description
            ? Str::limit($description, 120)
            : __('Use the recommended tool to make measurable progress on :pillar.', ['pillar' => __($pillar)]);

        $translated = trans("coach.tool_guide.{$moduleKey}", ['pillar' => __($pillar)]);

        return $translated === "coach.tool_guide.{$moduleKey}" ? $fallback : $translated;
    }

    protected function knowledge(?array $focus): ?array
    {
        if (! $focus) {
            return null;
        }

        $term = $this->pickTerm($focus);

        if (! $term) {
            return null;
        }

        $definition = $term['simple_definition'] ?: $term['definition'];

        return [
            'term' => $term['term'],
            'slug' => $term['slug'],
            'definition' => Str::limit(strip_tags($definition), 180),
            'link' => route('knowledge.show', $term['slug']),
            'is_beginner_friendly' => $term['is_beginner_friendly'],
        ];
    }

    protected function pickTerm(?array $focus): ?array
    {
        if (! $focus) {
            return null;
        }

        $terms = ! empty($focus['glossary_terms']) && $focus['glossary_terms'] instanceof Collection
            ? $focus['glossary_terms']
            : collect();

        if ($terms->isEmpty() && $focus['module_key']) {
            $terms = $this->glossaryService->relatedForModule($focus['module_key'], 1);
        }

        if ($terms->isEmpty()) {
            $terms = $this->glossaryService->relatedForPillar($focus['pillar'], 1);
        }

        if ($terms->isEmpty()) {
            $terms = GlossaryTerm::published()
                ->where('slug', 'allocore-score')
                ->limit(1)
                ->get();
        }

        return $terms->first()?->toArray();
    }

    protected function buildImprovement(AllocoreScore $score, array $focus): array
    {
        return [
            'pillar' => $focus['pillar'] ?? null,
            'priority' => $focus['priority'] ?? null,
            'problem' => $this->problem($focus),
            'tool' => $this->tool($focus),
            'knowledge' => $this->knowledge($focus),
            'benchmark' => $this->pillarBenchmark($score, $focus['pillar'] ?? ''),
        ];
    }

    protected function benchmark(AllocoreScore $score): ?array
    {
        if (! $score->industry) {
            return null;
        }

        $stats = AllocoreBenchmarkService::industryStats($score->industry, $score->industry_sub);
        $percentile = AllocoreBenchmarkService::percentile($score);

        if (($stats['count'] ?? 0) === 0 && $percentile === null) {
            return null;
        }

        $parts = array_filter([$score->industry, $score->size]);

        return [
            'percentile' => $percentile,
            'average' => $stats['average'] ?? null,
            'count' => $stats['count'] ?? 0,
            'cluster' => implode(' · ', $parts),
        ];
    }

    protected function pillarBenchmark(AllocoreScore $score, string $pillar): ?array
    {
        if (! $score->industry || ! $pillar) {
            return null;
        }

        $stats = AllocoreBenchmarkService::pillarStats($score->industry, $score->industry_sub, $pillar);

        if (($stats['count'] ?? 0) === 0) {
            return null;
        }

        $userScore = (float) (collect($score->pillars ?? [])->firstWhere('name', $pillar)['score'] ?? 0);
        $average = (float) ($stats['average'] ?? 0);
        $diff = round($userScore - $average, 1);

        return [
            'average' => $average,
            'count' => $stats['count'],
            'diff' => $diff,
            'better' => $diff > 0.01,
            'worse' => $diff < -0.01,
        ];
    }

    protected function strongestPillar(AllocoreScore $score): ?array
    {
        $pillars = collect($score->pillars ?? []);

        if ($pillars->isEmpty()) {
            return null;
        }

        return $pillars->sortByDesc('score')->first();
    }
}
