<?php

namespace App\Services;

use App\Models\AllocoreScore;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Str;

class AllocoreCoachService
{
    public function __construct(
        private QuestionRecommendationService $questionRecommendationService,
    ) {}

    public function forScore(?AllocoreScore $score, User $user): array
    {
        if (! $score) {
            return [
                'has_score' => false,
                'cta' => __('Start an audit to get your personal Allocore Coach.'),
            ];
        }

        $questions = $this->questionRecommendationService->gapsForScore($score, $user);
        $focus = $questions[0] ?? null;
        $trend = $this->trend($score, $user);
        $strongest = $this->strongestPillar($score);

        return [
            'has_score' => true,
            'trend' => $trend,
            'benchmark' => $this->benchmark($score),
            'positive' => $this->positive($score, $strongest),
            'problem' => $focus ? $this->problemForQuestion($focus) : null,
            'tool' => $focus ? $this->toolForQuestion($focus) : null,
            'knowledge' => $focus ? $focus['knowledge'] : null,
            'history' => null,
            'all' => array_map(fn (array $q) => $this->buildImprovement($q), $questions),
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

    protected function problemForQuestion(array $question): array
    {
        return [
            'pillar' => $question['pillar'],
            'score' => $question['score'],
            'headline' => $question['question'],
            'solution' => $question['manual'],
        ];
    }

    protected function toolForQuestion(array $question): ?array
    {
        if (! $question['module_key']) {
            return null;
        }

        $module = Module::byKey($question['module_key']);

        return [
            'name' => $question['module_name'],
            'key' => $question['module_key'],
            'route' => $question['module_route'],
            'subscribed' => $question['subscribed'],
            'guide' => $this->toolGuide($question['module_key'], $question['pillar'], $question['module_description'] ?? $module?->description),
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

    protected function buildImprovement(array $question): array
    {
        return [
            'pillar' => $question['pillar'],
            'priority' => $question['priority'],
            'problem' => $this->problemForQuestion($question),
            'tool' => $this->toolForQuestion($question),
            'knowledge' => $question['knowledge'],
            'benchmark' => $question['benchmark'],
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

    protected function strongestPillar(AllocoreScore $score): ?array
    {
        $pillars = collect($score->pillars ?? []);

        if ($pillars->isEmpty()) {
            return null;
        }

        return $pillars->sortByDesc('score')->first();
    }
}
