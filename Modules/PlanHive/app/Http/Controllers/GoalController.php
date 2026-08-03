<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\PlanHive\Models\Goal;
use Modules\PlanHive\Models\Project;

class GoalController extends Controller
{
    public function index(Project $project): View
    {
        $goals = $project->goals()->with('project')->latest()->paginate(25);

        return view('planhive::goals.index', compact('project', 'goals'));
    }

    public function show(Goal $goal): View
    {
        return view('planhive::goals.show', compact('goal'));
    }

    public function create(Project $project): View
    {
        return view('planhive::goals.form', ['project' => $project, 'goal' => new Goal]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string|in:active,achieved,dropped',
        ]);

        $validated['status'] ??= 'active';
        $validated['progress'] ??= 0;

        $project->goals()->create($validated + ['team_id' => $project->team_id, 'user_id' => auth()->id()]);

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Goal created.'));
    }

    public function edit(Goal $goal): View
    {
        return view('planhive::goals.form', ['project' => $goal->project, 'goal' => $goal]);
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string|in:active,achieved,dropped',
        ]);

        $validated['status'] ??= $goal->status ?? 'active';
        $validated['progress'] ??= $goal->progress ?? 0;

        $goal->update($validated);

        return redirect()->route('planhive.goals.show', $goal)->with('success', __('Goal updated.'));
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        $project = $goal->project;
        $goal->delete();

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Goal deleted.'));
    }
}
