<?php

namespace Modules\SweetSpot\Services;

use Illuminate\Support\Facades\DB;
use Modules\SweetSpot\Models\Customer;
use Modules\SweetSpot\Models\CustomerScore;
use Modules\SweetSpot\Models\SettingsWeight;

class SweetSpotScoringService
{
    public function calculateAll(?int $teamId = null): void
    {
        DB::transaction(function () use ($teamId) {
            $customers = Customer::query()
                ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
                ->get();

            if ($customers->isEmpty()) {
                return;
            }

            $firstCustomer = $customers->first();
            $actualTeamId = $teamId ?? $firstCustomer->team_id;

            $metrics = [
                'margins' => [],
                'efforts' => [],
                'repeats' => [],
                'recommendations' => [],
            ];

            foreach ($customers as $customer) {
                $marginPerHour = ($customer->effort_hours > 0)
                    ? ($customer->profit_margin_eur / $customer->effort_hours)
                    : 0;

                $metrics['margins'][$customer->id] = $marginPerHour;
                $metrics['efforts'][$customer->id] = $customer->effort_hours ?? 0;
                $metrics['repeats'][$customer->id] = $customer->repeat_rate ?? 0;
                $metrics['recommendations'][$customer->id] = $customer->recommendations ?? 0;
            }

            $mins = [
                'margin' => min($metrics['margins']),
                'effort' => min($metrics['efforts']),
                'repeat' => min($metrics['repeats']),
                'recommendation' => min($metrics['recommendations']),
            ];

            $maxs = [
                'margin' => max($metrics['margins']),
                'effort' => max($metrics['efforts']),
                'repeat' => max($metrics['repeats']),
                'recommendation' => max($metrics['recommendations']),
            ];

            $weights = $this->weightsForTeam($actualTeamId);

            $w = [
                'profitability' => $weights['profitability'] ?? 3,
                'effort' => $weights['effort'] ?? 2,
                'chemistry' => $weights['chemistry'] ?? 2,
                'growth' => $weights['growth'] ?? 3,
                'repeat' => $weights['repeat'] ?? 2,
                'recommendation' => $weights['recommendation'] ?? 1,
                'payment' => $weights['payment'] ?? 2,
            ];

            $totalWeight = array_sum($w) ?: 1;
            $customerScoresData = [];

            foreach ($customers as $customer) {
                $marginPerHour = $metrics['margins'][$customer->id];
                $effortHours = $metrics['efforts'][$customer->id];
                $repeatRate = $metrics['repeats'][$customer->id];
                $recommendations = $metrics['recommendations'][$customer->id];

                $scores = [
                    'profitability' => $this->normalizeTo5($marginPerHour, $mins['margin'], $maxs['margin']),
                    'effort' => $this->normalizeTo5Inverse($effortHours, $mins['effort'], $maxs['effort']),
                    'chemistry' => $customer->chemistry_score ?? 3,
                    'growth' => $customer->growth_score ?? 3,
                    'repeat' => $this->normalizeTo5($repeatRate, $mins['repeat'], $maxs['repeat']),
                    'recommendation' => $this->normalizeTo5($recommendations, $mins['recommendation'], $maxs['recommendation']),
                    'payment' => $customer->payment_willingness ?? 3,
                ];

                $totalScore = (
                    ($scores['profitability'] * $w['profitability']) +
                    ($scores['effort'] * $w['effort']) +
                    ($scores['chemistry'] * $w['chemistry']) +
                    ($scores['growth'] * $w['growth']) +
                    ($scores['repeat'] * $w['repeat']) +
                    ($scores['recommendation'] * $w['recommendation']) +
                    ($scores['payment'] * $w['payment'])
                ) / $totalWeight;

                $customerScoresData[] = [
                    'customer_id' => $customer->id,
                    'team_id' => $customer->team_id,
                    'margin_per_hour' => $marginPerHour,
                    'profitability_score' => $scores['profitability'],
                    'effort_score' => $scores['effort'],
                    'chemistry_score' => $scores['chemistry'],
                    'growth_score' => $scores['growth'],
                    'repeat_score' => $scores['repeat'],
                    'recommendation_score' => $scores['recommendation'],
                    'payment_score' => $scores['payment'],
                    'total_score' => round($totalScore, 2),
                ];
            }

            usort($customerScoresData, function ($a, $b) {
                return $b['total_score'] <=> $a['total_score'];
            });

            $totalCustomers = count($customerScoresData);
            $top20Count = ceil($totalCustomers * 0.20);
            $now = now();

            foreach ($customerScoresData as $index => $data) {
                $rank = $index + 1;
                $isTop = $rank <= $top20Count;

                CustomerScore::updateOrCreate(
                    ['customer_id' => $data['customer_id']],
                    [
                        ...$data,
                        'rank' => $rank,
                        'top_flag' => $isTop,
                        'calculated_at' => $now,
                    ]
                );
            }
        });
    }

    public function weightsForTeam(int $teamId): array
    {
        $keys = ['profitability', 'effort', 'chemistry', 'growth', 'repeat', 'recommendation', 'payment'];
        $defaults = [
            'profitability' => 3,
            'effort' => 2,
            'chemistry' => 2,
            'growth' => 3,
            'repeat' => 2,
            'recommendation' => 1,
            'payment' => 2,
        ];

        $existing = SettingsWeight::where('team_id', $teamId)
            ->whereIn('criterion_key', $keys)
            ->pluck('weight', 'criterion_key')
            ->toArray();

        foreach ($keys as $key) {
            if (! isset($existing[$key])) {
                $existing[$key] = $defaults[$key];
            }
        }

        return $existing;
    }

    public function ensureDefaultWeights(int $teamId): void
    {
        $defaults = [
            'profitability' => 3,
            'effort' => 2,
            'chemistry' => 2,
            'growth' => 3,
            'repeat' => 2,
            'recommendation' => 1,
            'payment' => 2,
        ];

        foreach ($defaults as $key => $weight) {
            SettingsWeight::firstOrCreate(
                ['team_id' => $teamId, 'criterion_key' => $key],
                ['weight' => $weight]
            );
        }
    }

    private function normalizeTo5($value, $min, $max)
    {
        if ($max == $min) {
            return 3;
        }

        return round((($value - $min) / ($max - $min)) * 4 + 1, 2);
    }

    private function normalizeTo5Inverse($value, $min, $max)
    {
        if ($max == $min) {
            return 3;
        }

        return round((($max - $value) / ($max - $min)) * 4 + 1, 2);
    }
}
