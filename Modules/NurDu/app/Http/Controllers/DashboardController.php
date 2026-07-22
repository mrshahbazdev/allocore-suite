<?php

namespace Modules\NurDu\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\NurDu\Models\Decision;
use Modules\NurDu\Models\QuarterlyFocus;
use Modules\NurDu\Models\Vision;
use Modules\NurDu\Models\VisionCheck;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $teamId = $request->user()->current_team_id;

        $vision = Vision::where('team_id', $teamId)
            ->with('guidingPrinciples')
            ->first();

        $currentQuarter = 'Q'.ceil(now()->month / 3);
        $currentYear = now()->year;

        $quarterlyFocus = QuarterlyFocus::where('team_id', $teamId)
            ->where('quarter', $currentQuarter)
            ->where('year', $currentYear)
            ->with('strategicPriorities')
            ->first();

        $recentDecisions = Decision::where('team_id', $teamId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $decisionStats = [
            'green' => Decision::where('team_id', $teamId)->where('alignment', 'green')->count(),
            'yellow' => Decision::where('team_id', $teamId)->where('alignment', 'yellow')->count(),
            'red' => Decision::where('team_id', $teamId)->where('alignment', 'red')->count(),
        ];

        $latestCheck = VisionCheck::where('team_id', $teamId)
            ->with('actionItems')
            ->orderByDesc('check_date')
            ->first();

        return view('nurdu::dashboard.index', compact(
            'vision',
            'quarterlyFocus',
            'currentQuarter',
            'currentYear',
            'recentDecisions',
            'decisionStats',
            'latestCheck'
        ));
    }
}
