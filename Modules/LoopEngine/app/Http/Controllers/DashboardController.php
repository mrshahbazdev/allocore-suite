<?php

namespace Modules\LoopEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;
use Modules\LoopEngine\Models\Process;
use Modules\LoopEngine\Models\ProcessRun;
use Modules\LoopEngine\Models\TeamAssignment;

class DashboardController extends Controller
{
    public function index(): View
    {
        $teamId = auth()->user()->current_team_id;

        $processes = Process::where('team_id', $teamId)->where('status', 'active')->count();
        $runs = ProcessRun::where('team_id', $teamId)->count();
        $completed = ProcessRun::where('team_id', $teamId)->where('status', 'completed')->count();
        $pendingAssignments = TeamAssignment::where('team_id', $teamId)->where('status', '!=', 'completed')->count();

        $recentRuns = ProcessRun::with('process')
            ->where('team_id', $teamId)
            ->latest('started_at')
            ->take(10)
            ->get();

        $runsByDay = ProcessRun::where('team_id', $teamId)
            ->where('started_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(started_at) as day, count(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day');

        $teamMembers = User::whereHas('teams', fn ($query) => $query->where('teams.id', $teamId))->take(5)->get();

        return view('loopengine::dashboard.index', compact('processes', 'runs', 'completed', 'pendingAssignments', 'recentRuns', 'runsByDay', 'teamMembers'));
    }
}
