<?php

namespace Modules\LeadQuality\Services;

use Carbon\Carbon;
use Modules\LeadQuality\Models\Activity;
use Modules\LeadQuality\Models\Contact;

class LeadQualityAnalyticsService
{
    public function getNetworkGrowth(): array
    {
        $teamId = auth()->user()?->current_team_id;

        return collect(range(0, 5))->map(function (int $i) use ($teamId) {
            $month = Carbon::now()->subMonths($i);

            return [
                'label' => $month->format('M Y'),
                'count' => Contact::query()
                    ->where('team_id', $teamId)
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
            ];
        })->reverse()->values()->toArray();
    }

    public function getFulfillmentRates(): array
    {
        $teamId = auth()->user()?->current_team_id;

        $totalActivitiesLast30Days = Activity::query()
            ->where('team_id', $teamId)
            ->where('scheduled_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $completedActivitiesLast30Days = Activity::query()
            ->where('team_id', $teamId)
            ->where('scheduled_at', '>=', Carbon::now()->subDays(30))
            ->where('status', 'completed')
            ->count();

        $successRate = $totalActivitiesLast30Days > 0
            ? ($completedActivitiesLast30Days / $totalActivitiesLast30Days) * 100
            : 0;

        return [
            'success_rate' => round($successRate, 1),
            'total_completed' => $completedActivitiesLast30Days,
        ];
    }

    public function getIndustrySuccessMap(): array
    {
        $teamId = auth()->user()?->current_team_id;
        $scoreEngine = new LeadScoreEngine;

        $goodLeads = Contact::query()->where('team_id', $teamId)->get()->filter(function (Contact $contact) use ($scoreEngine) {
            return $scoreEngine->calculateScore($contact)['total_score'] >= 70;
        });

        return $goodLeads->groupBy('industry')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->take(5)
            ->toArray();
    }
}
