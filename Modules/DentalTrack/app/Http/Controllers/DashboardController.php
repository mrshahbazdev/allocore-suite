<?php

namespace Modules\DentalTrack\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\DentalTrack\Enums\OrderStatus;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\Workstation;
use Modules\DentalTrack\Services\DashboardCacheService;

class DashboardController extends Controller
{
    public function index(DashboardCacheService $cache): View
    {
        $counts = $cache->getOrderCounts();
        $inProgress = Order::with(['lab', 'productType', 'steps'])
            ->where('status', OrderStatus::InProgress)
            ->orderByDesc('priority')
            ->orderBy('due_date')
            ->limit(20)
            ->get();

        $pending = Order::with(['lab', 'productType'])
            ->where('status', OrderStatus::Pending)
            ->orderByDesc('priority')
            ->orderBy('due_date')
            ->limit(20)
            ->get();

        $overdue = Order::with(['lab', 'productType'])
            ->where('due_date', '<', now())
            ->whereNotIn('status', [OrderStatus::Completed, OrderStatus::Cancelled])
            ->orderBy('due_date')
            ->limit(20)
            ->get();

        $workstations = Workstation::where('is_active', true)
            ->with('lab')
            ->orderBy('name')
            ->get();

        return view('dentaltrack::dashboard.index', compact('counts', 'inProgress', 'pending', 'overdue', 'workstations'));
    }
}
