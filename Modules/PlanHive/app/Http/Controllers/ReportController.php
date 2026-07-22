<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\PlanHive\Models\Goal;
use Modules\PlanHive\Models\Project;
use Modules\PlanHive\Models\Task;

class ReportController extends Controller
{
    public function index(): View
    {
        $team = auth()->user()->currentTeam;

        $projects = Project::query()->count();
        $tasks = Task::query()->count();
        $doneTasks = Task::query()->where('status', 'done')->count();
        $activeGoals = Goal::query()->where('status', 'active')->count();

        $statusCounts = Task::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $priorityCounts = Task::query()
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        return view('planhive::reports.index', compact('projects', 'tasks', 'doneTasks', 'activeGoals', 'statusCounts', 'priorityCounts'));
    }
}
