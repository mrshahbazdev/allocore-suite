<?php

namespace App\Services;

use App\Models\AllocoreScore;
use App\Models\Module;
use App\Models\User;

class AllocoreRecommendationService
{
    /**
     * Pillar-to-capability mapping in the Allocore needs pyramid order.
     * Address lower levels before higher ones.
     */
    protected array $pyramidOrder = ['Revenue', 'Profit', 'Order', 'Influence', 'Legacy'];

    protected array $pillarMap = [
        'Revenue' => [
            'modules' => ['financial-platform', 'invoice-maker', 'sweet-spot'],
            'action' => 'recommendations.revenue_action',
            'kpis' => [
                ['label' => 'Umsatzreife', 'unit' => 'score', 'target' => 80],
                ['label' => 'Umsatzwachstum', 'unit' => '%', 'target' => 15],
            ],
        ],
        'Profit' => [
            'modules' => ['cash-core', 'financial-platform', 'sweet-spot'],
            'action' => 'recommendations.profit_action',
            'kpis' => [
                ['label' => 'Profitabilität', 'unit' => 'score', 'target' => 80],
                ['label' => 'Deckungsbeitrag', 'unit' => '%', 'target' => 25],
            ],
        ],
        'Order' => [
            'modules' => ['plan-hive', 'time-butler', 'loop-engine', 'focus-matrix'],
            'action' => 'recommendations.order_action',
            'kpis' => [
                ['label' => 'Betriebliche Ordnung', 'unit' => 'score', 'target' => 80],
                ['label' => 'Prozess-Compliance', 'unit' => '%', 'target' => 90],
            ],
        ],
        'Influence' => [
            'modules' => ['keyword-cluster', 'lead-quality', 'bunny-band'],
            'action' => 'recommendations.influence_action',
            'kpis' => [
                ['label' => 'Markteinfluss', 'unit' => 'score', 'target' => 80],
                ['label' => 'Lead-Qualität', 'unit' => 'score', 'target' => 80],
            ],
        ],
        'Legacy' => [
            'modules' => ['vision-flow', 'nur-du', 'org-matrix'],
            'action' => 'recommendations.legacy_action',
            'kpis' => [
                ['label' => 'Unternehmensvermächtnis', 'unit' => 'score', 'target' => 80],
                ['label' => 'Führungskultur', 'unit' => 'score', 'target' => 80],
            ],
        ],
    ];

    public function __construct(private GlossaryService $glossaryService) {}

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

        $pillarsByName = collect($score->pillars ?? [])->keyBy('name');

        $weakThreshold = 20;
        $sorted = collect($this->pyramidOrder)
            ->map(fn ($name) => $pillarsByName->get($name))
            ->filter()
            ->values();

        $weak = $sorted->filter(fn (array $pillar) => ($pillar['score'] ?? 0) < $weakThreshold)->values();

        if ($weak->isEmpty()) {
            $weak = $sorted->take(1);
        }

        $items = $weak
            ->take(3)
            ->values()
            ->map(function (array $pillar, int $index) use ($modules, $subscribedKeys) {
                $map = $this->pillarMap[$pillar['name']] ?? ['modules' => [], 'action' => '', 'kpis' => []];
                $target = collect($map['modules'])->first(fn ($key) => $modules->has($key));
                $module = $target ? $modules->get($target) : null;
                $subscribed = $target && in_array($target, $subscribedKeys, true);

                return [
                    'priority' => $index + 1,
                    'is_first' => $index === 0,
                    'pillar' => $pillar['name'],
                    'score' => $pillar['score'],
                    'maturity' => $pillar['maturity'],
                    'action' => $map['action'],
                    'action_html' => $this->glossaryService->linkTerms(__($map['action'])),
                    'module_key' => $module?->key,
                    'module_name' => $module?->name,
                    'module_route' => $module?->route_prefix ? url('app/'.$module->route_prefix) : null,
                    'subscribed' => $subscribed,
                    'glossary_terms' => $this->glossaryService->relatedForPillar($pillar['name']),
                    'kpis' => $this->buildKpis($pillar['score'], $map['kpis'] ?? []),
                ];
            })
            ->all();

        $headline = $this->headline($score->score);

        return compact('headline', 'items');
    }

    protected function buildKpis(float $score, array $definitions): array
    {
        return collect($definitions)->map(function (array $kpi) use ($score) {
            $current = $kpi['unit'] === 'score' ? $score : $this->estimateKpiValue($score, $kpi);
            $target = $kpi['target'];

            return [
                'label' => $kpi['label'],
                'unit' => $kpi['unit'],
                'current' => round($current, 1),
                'target' => $target,
                'gap' => max(0, $target - $current),
                'progress' => $target > 0 ? min(100, ($current / $target) * 100) : 0,
            ];
        })->all();
    }

    protected function estimateKpiValue(float $score, array $kpi): float
    {
        $ratio = $score / 100;

        return $kpi['target'] * $ratio;
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
