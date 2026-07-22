<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\PlanHive\Models\CalendarEvent;
use Modules\PlanHive\Models\Project;
use Modules\PlanHive\Models\Reminder;
use Modules\PlanHive\Models\Task;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $team = $user->currentTeam;

        $projects = Project::query()->withCount(['tasks', 'goals'])->latest()->take(6)->get();

        $myTasks = Task::query()
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['todo', 'in_progress'])
            ->with('project')
            ->latest('due_date')
            ->take(8)
            ->get();

        $upcomingEvents = CalendarEvent::query()
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        $reminders = Reminder::query()
            ->where('user_id', $user->id)
            ->where('is_done', false)
            ->orderBy('remind_at')
            ->take(5)
            ->get();

        $stats = [
            'projects' => Project::query()->count(),
            'tasks' => Task::query()->count(),
            'done' => Task::query()->where('status', 'done')->count(),
            'events' => CalendarEvent::query()->whereBetween('start_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];

        return view('planhive::dashboard.index', compact('projects', 'myTasks', 'upcomingEvents', 'reminders', 'stats'));
    }
}
