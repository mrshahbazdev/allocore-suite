<?php

namespace App\Support;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;

class ModuleRecommender
{
    /**
     * Module tags used to compute similarity.
     */
    protected array $tags = [
        'invoice-maker' => ['finance', 'billing', 'revenue'],
        'audit' => ['governance', 'compliance', 'assessment'],
        'keyword-cluster' => ['seo', 'marketing', 'content'],
        'lead-quality' => ['sales', 'crm', 'outreach'],
        'time-butler' => ['people', 'hr', 'time', 'operations'],
        'plan-hive' => ['project', 'productivity', 'operations'],
        'kpi-tool' => ['analytics', 'kpi', 'performance'],
        'loop-engine' => ['process', 'operations', 'sop'],
        'smart-kpi' => ['analytics', 'kpi', 'performance', 'strategy'],
        'cash-core' => ['finance', 'cash', 'profit'],
        'bunny-band' => ['rewards', 'referral', 'community'],
        'dental-track' => ['manufacturing', 'operations', 'tracking'],
        'focus-matrix' => ['productivity', 'tasks', 'manager'],
        'org-matrix' => ['people', 'org', 'structure'],
        'vision-flow' => ['strategy', 'vision', 'culture'],
        'nur-du' => ['strategy', 'vision', 'focus'],
        'financial-platform' => ['finance', 'analytics', 'revenue', 'kpi'],
        'sweet-spot' => ['sales', 'finance', 'customer', 'scoring'],
    ];

    /**
     * Pairs of modules that unlock a missing third one.
     */
    protected array $combos = [
        ['modules' => ['invoice-maker', 'cash-core'], 'suggest' => 'financial-platform', 'reason_key' => 'recommendations.combo_finance'],
        ['modules' => ['lead-quality', 'invoice-maker'], 'suggest' => 'sweet-spot', 'reason_key' => 'recommendations.combo_customer_score'],
        ['modules' => ['plan-hive', 'time-butler'], 'suggest' => 'focus-matrix', 'reason_key' => 'recommendations.combo_capacity'],
        ['modules' => ['audit', 'smart-kpi'], 'suggest' => 'kpi-tool', 'reason_key' => 'recommendations.combo_governance'],
        ['modules' => ['keyword-cluster', 'lead-quality'], 'suggest' => 'bunny-band', 'reason_key' => 'recommendations.combo_growth_referral'],
        ['modules' => ['dental-track', 'invoice-maker'], 'suggest' => 'cash-core', 'reason_key' => 'recommendations.combo_lab_billing'],
        ['modules' => ['vision-flow', 'plan-hive'], 'suggest' => 'nur-du', 'reason_key' => 'recommendations.combo_strategy'],
        ['modules' => ['org-matrix', 'time-butler'], 'suggest' => 'plan-hive', 'reason_key' => 'recommendations.combo_people_projects'],
        ['modules' => ['loop-engine', 'plan-hive'], 'suggest' => 'focus-matrix', 'reason_key' => 'recommendations.combo_execution'],
        ['modules' => ['focus-matrix', 'plan-hive'], 'suggest' => 'time-butler', 'reason_key' => 'recommendations.combo_productivity'],
        ['modules' => ['kpi-tool', 'smart-kpi'], 'suggest' => 'financial-platform', 'reason_key' => 'recommendations.combo_kpi_finance'],
        ['modules' => ['cash-core', 'financial-platform'], 'suggest' => 'audit', 'reason_key' => 'recommendations.combo_audit_finance'],
        ['modules' => ['lead-quality', 'bunny-band'], 'suggest' => 'sweet-spot', 'reason_key' => 'recommendations.combo_referral_score'],
        ['modules' => ['vision-flow', 'nur-du'], 'suggest' => 'org-matrix', 'reason_key' => 'recommendations.combo_culture_structure'],
    ];

    public function forUser(User $user): array
    {
        $modules = Module::where('is_active', true)->get()->keyBy('key');
        $subscribedKeys = $modules->keys()->filter(fn ($key) => $user->hasModule($key))->all();

        return [
            'similar' => $this->similarModules($modules, $subscribedKeys),
            'combos' => $this->comboSuggestions($modules, $subscribedKeys),
        ];
    }

    protected function similarModules(Collection $modules, array $subscribedKeys): array
    {
        $suggestions = [];

        foreach ($subscribedKeys as $owned) {
            $ownedTags = $this->tags[$owned] ?? [];

            foreach ($modules as $key => $module) {
                if ($key === $owned || in_array($key, $subscribedKeys, true)) {
                    continue;
                }

                $score = $this->tagSimilarity($ownedTags, $this->tags[$key] ?? []);

                if ($score <= 0) {
                    continue;
                }

                if (! isset($suggestions[$key])) {
                    $suggestions[$key] = ['score' => 0, 'reasons' => []];
                }

                $suggestions[$key]['score'] += $score;
                $suggestions[$key]['reasons'][] = __('recommendations.because_you_use', ['module' => $modules->get($owned)?->name ?? $owned]);
            }
        }

        arsort($suggestions);

        return collect($suggestions)
            ->take(6)
            ->map(function ($data, $key) use ($modules) {
                $module = $modules->get($key);

                return [
                    'key' => $key,
                    'name' => $module?->name ?? $key,
                    'description' => $module?->description,
                    'route_prefix' => $module?->route_prefix,
                    'score' => round($data['score'], 2),
                    'reason' => collect($data['reasons'])->unique()->implode(', '),
                ];
            })
            ->values()
            ->all();
    }

    protected function comboSuggestions(Collection $modules, array $subscribedKeys): array
    {
        return collect($this->combos)
            ->filter(fn ($combo) => in_array($combo['suggest'], $subscribedKeys, true) === false)
            ->filter(fn ($combo) => collect($combo['modules'])->intersect($subscribedKeys)->isNotEmpty())
            ->map(function ($combo) use ($modules, $subscribedKeys) {
                $owned = collect($combo['modules'])->intersect($subscribedKeys)->values()->all();
                $missing = collect($combo['modules'])->diff($subscribedKeys)->values()->all();
                $suggestModule = $modules->get($combo['suggest']);

                return [
                    'suggest_key' => $combo['suggest'],
                    'suggest_name' => $suggestModule?->name ?? $combo['suggest'],
                    'suggest_description' => $suggestModule?->description,
                    'suggest_route_prefix' => $suggestModule?->route_prefix,
                    'owned_modules' => $owned,
                    'missing_modules' => $missing,
                    'reason' => __($combo['reason_key']),
                ];
            })
            ->sortByDesc(fn ($combo) => count($combo['owned_modules']))
            ->take(6)
            ->values()
            ->all();
    }

    protected function tagSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0;
        }

        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));

        return $union ? round($intersection / $union, 2) : 0;
    }
}
