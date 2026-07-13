<?php

namespace Modules\LeadQuality\Http\Controllers;

use Illuminate\View\View;
use Modules\LeadQuality\Services\DashboardSnapshot;
use Modules\LeadQuality\Services\LeadQualityAnalyticsService;

class DashboardController
{
    public function __invoke(DashboardSnapshot $snapshot, LeadQualityAnalyticsService $analyticsService): View
    {
        $team = auth()->user()?->currentTeam;

        return view('leadquality::dashboard', [
            'snapshot' => $snapshot->forTeam($team),
            'growthData' => $analyticsService->getNetworkGrowth(),
            'fulfillment' => $analyticsService->getFulfillmentRates(),
            'industryMap' => $analyticsService->getIndustrySuccessMap(),
        ]);
    }
}
