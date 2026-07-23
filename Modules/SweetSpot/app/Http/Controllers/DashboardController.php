<?php

namespace Modules\SweetSpot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SweetSpot\Models\Customer;
use Modules\SweetSpot\Models\CustomerScore;
use Modules\SweetSpot\Services\SweetSpotScoringService;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $topCustomers = CustomerScore::with('customer')
            ->where('team_id', $request->user()->current_team_id)
            ->orderBy('total_score', 'desc')
            ->take(10)
            ->get();

        $averageScore = CustomerScore::where('team_id', $request->user()->current_team_id)->avg('total_score');

        $customerCount = Customer::where('team_id', $request->user()->current_team_id)->count();
        $calculatedAt = CustomerScore::where('team_id', $request->user()->current_team_id)->max('calculated_at');

        return view('sweetspot::dashboard.index', compact('topCustomers', 'averageScore', 'customerCount', 'calculatedAt'));
    }

    public function recalculate(Request $request, SweetSpotScoringService $service)
    {
        $service->calculateAll($request->user()->current_team_id);

        return redirect()->route('sweetspot.dashboard')->with('success', __('Scores recalculated.'));
    }
}
