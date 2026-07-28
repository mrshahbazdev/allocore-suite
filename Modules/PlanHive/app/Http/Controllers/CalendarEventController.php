<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\PlanHive\Models\CalendarEvent;
use Modules\PlanHive\Models\Project;

class CalendarEventController extends Controller
{
    public function index(Request $request, ?Project $project = null): View
    {
        $month = $request->get('month', now()->format('Y-m'));
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $query = CalendarEvent::query()
            ->whereBetween('start_at', [$start, $end])
            ->with('project')
            ->orderBy('start_at');

        if ($project) {
            $query->where('project_id', $project->id);
        }

        $events = $query->get();

        $projects = Project::query()->get();

        return view('planhive::calendar.index', compact('events', 'start', 'end', 'month', 'project', 'projects'));
    }

    public function create(Project $project): View
    {
        return view('planhive::calendar.form', ['project' => $project, 'event' => new CalendarEvent]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'nullable|boolean',
        ]);

        $validated['all_day'] = $request->boolean('all_day');

        $project->calendarEvents()->create($validated + ['team_id' => $project->team_id, 'user_id' => auth()->id()]);

        return redirect()->route('planhive.calendar.index')->with('success', __('Event created.'));
    }

    public function storeGlobal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:planhive_projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'nullable|boolean',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $validated['all_day'] = $request->boolean('all_day');

        $project->calendarEvents()->create($validated + ['team_id' => $project->team_id, 'user_id' => auth()->id()]);

        return redirect()->route('planhive.calendar.index', ['month' => now()->format('Y-m')])->with('success', __('Event created.'));
    }

    public function edit(CalendarEvent $calendarEvent): View
    {
        return view('planhive::calendar.form', ['project' => $calendarEvent->project, 'event' => $calendarEvent]);
    }

    public function update(Request $request, CalendarEvent $calendarEvent): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'nullable|boolean',
        ]);

        $validated['all_day'] = $request->boolean('all_day');
        $calendarEvent->update($validated);

        return redirect()->route('planhive.calendar.index')->with('success', __('Event updated.'));
    }

    public function destroy(CalendarEvent $calendarEvent): RedirectResponse
    {
        $calendarEvent->delete();

        return redirect()->route('planhive.calendar.index')->with('success', __('Event deleted.'));
    }
}
