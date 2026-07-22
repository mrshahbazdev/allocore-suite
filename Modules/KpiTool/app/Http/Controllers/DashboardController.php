<?php

namespace Modules\KpiTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\KpiTool\Models\KpiDefinition;
use Modules\KpiTool\Models\KpiValue;

class DashboardController extends Controller
{
    public function index(): View
    {
        $definitions = KpiDefinition::query()->where('is_active', true)->count();

        $values = KpiValue::query()->count();
        $latestValues = KpiValue::query()
            ->with('kpiDefinition')
            ->latest('recorded_at')
            ->take(10)
            ->get();

        $statusCounts = KpiValue::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $topKpis = KpiDefinition::query()
            ->with('latestValue')
            ->where('is_active', true)
            ->take(6)
            ->get();

        return view('kpitool::dashboard.index', compact('definitions', 'values', 'latestValues', 'statusCounts', 'topKpis'));
    }
}
