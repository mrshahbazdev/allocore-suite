<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\SmartKpi\Models\Action;
use Modules\SmartKpi\Models\Company;
use Modules\SmartKpi\Models\KpiDefinition;
use Modules\SmartKpi\Models\Problem;

class DashboardController extends Controller
{
    public function index(): View
    {
        $teamId = auth()->user()->current_team_id;

        $companies = Company::count();
        $kpis = KpiDefinition::where('is_template', false)->count();
        $problems = Problem::where('status', 'open')->count();
        $openActions = Action::where('status', '!=', 'done')->count();

        $recentValues = KpiDefinition::with('latestValue')
            ->where('is_template', false)
            ->latest()
            ->take(10)
            ->get();

        $problemsBySeverity = Problem::where('status', 'open')
            ->selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity');

        return view('smartkpi::dashboard.index', compact('companies', 'kpis', 'problems', 'openActions', 'recentValues', 'problemsBySeverity'));
    }
}
