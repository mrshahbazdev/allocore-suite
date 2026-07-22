<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\DentalTrack\Services\DashboardCacheService;

class StationMonitoringController extends Controller
{
    public function __construct(
        private readonly DashboardCacheService $cache,
    ) {}

    public function index(): View
    {
        $statuses = $this->cache->getWorkstationStatus();

        return view('dentaltrack::admin.station-monitoring.index', compact('statuses'));
    }
}
