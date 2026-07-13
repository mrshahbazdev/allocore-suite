<?php

namespace Modules\LeadQuality\Http\Controllers;

use Illuminate\View\View;
use Modules\LeadQuality\Services\LeadQualityAnalyticsService;

class AnalyticsController
{
    public function __invoke(LeadQualityAnalyticsService $analyticsService): View
    {
        return view('leadquality::analytics.index', [
            'growthData' => $analyticsService->getNetworkGrowth(),
            'fulfillment' => $analyticsService->getFulfillmentRates(),
            'industryMap' => $analyticsService->getIndustrySuccessMap(),
        ]);
    }
}
