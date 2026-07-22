<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\DentalTrack\Enums\OrderStatus;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\ScanEvent;
use Modules\DentalTrack\Services\DashboardCacheService;
use Modules\DentalTrack\Services\PredictionService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardCacheService $cache,
        private readonly PredictionService $predictionService,
    ) {}

    public function index(): View
    {
        $counts = $this->cache->getOrderCounts();

        $orders = Order::with(['company', 'lab', 'productType', 'steps'])
            ->whereNotIn('status', [OrderStatus::Completed, OrderStatus::Cancelled])
            ->orderByDesc('priority')
            ->orderBy('due_date')
            ->paginate(50);

        $workstationStatus = $this->cache->getWorkstationStatus();
        $suggestions = $this->predictionService->getSmartSuggestions();

        $recentScans = ScanEvent::with(['order', 'workstation', 'user'])
            ->orderByDesc('scanned_at')
            ->limit(20)
            ->get();

        return view('dentaltrack::admin.dashboard.index', compact('counts', 'orders', 'workstationStatus', 'suggestions', 'recentScans'));
    }
}
