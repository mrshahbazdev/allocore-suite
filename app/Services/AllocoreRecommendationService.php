<?php

namespace App\Services;

use App\Models\AllocoreScore;
use App\Models\Module;
use App\Models\User;

class AllocoreRecommendationService
{
    /**
     * Pillar-to-capability mapping. Lower scores surface first.
     */
    protected array $pillarMap = [
        'Revenue' => [
            'modules' => ['financial-platform', 'invoice-maker', 'sweet-spot'],
            'action' => 'recommendations.revenue_action',
        ],
        'Profit' => [
            'modules' => ['cash-core', 'financial-platform', 'sweet-spot'],
            'action' => 'recommendations.profit_action',
        ],
        'Order' => [
            'modules' => ['plan-hive', 'time-butler', 'loop-engine', 'focus-matrix'],
            'action' => 'recommendations.order_action',
        ],
        'Influence' => [
            'modules' => ['keyword-cluster', 'lead-quality', 'bunny-band'],
            'action' => 'recommendations.influence_action',
        ],
        'Legacy' => [
            'modules' => ['vision-flow', 'nur-du', 'org-matrix'],
            'action' => 'recommendations.legacy_action',
        ],
    ];

    public function forScore(?AllocoreScore $score, User $user): array
    {
        if (! $score) {
            return [
                'headline' => __('recommendations.start_audit'),
                'items' => [],
            ];
        }

        $modules = Module::where('is_active', true)->get()->keyBy('key');
        $subscribedKeys = $modules->keys()->filter(fn ($key) => $user->hasModule($key))->all();

        $items = collect($score->pillars ?? [])
            ->sortBy('score')
            ->take(3)
            ->map(function (array $pillar) use ($modules, $subscribedKeys) {
                $map = $this->pillarMap[$pillar['name']] ?? ['modules' => [], 'action' => ''];
                $target = collect($map['modules'])->first(fn ($key) => $modules->has($key));
                $module = $target ? $modules->get($target) : null;
                $subscribed = $target && in_array($target, $subscribedKeys, true);

                return [
                    'pillar' => $pillar['name'],
                    'score' => $pillar['score'],
                    'maturity' => $pillar['maturity'],
                    'action' => $map['action'],
                    'module_key' => $module?->key,
                    'module_name' => $module?->name,
                    'module_route' => $module?->route_prefix ? url('app/'.$module->route_prefix) : null,
                    'subscribed' => $subscribed,
                ];
            })
            ->values()
            ->all();

        $headline = $this->headline($score->score);

        return compact('headline', 'items');
    }

    protected function headline(float $score): string
    {
        return match (true) {
            $score >= 80 => __('recommendations.headline_excellent'),
            $score >= 60 => __('recommendations.headline_strong'),
            $score >= 40 => __('recommendations.headline_solid'),
            $score >= 20 => __('recommendations.headline_weak'),
            default => __('recommendations.headline_critical'),
        };
    }
}
