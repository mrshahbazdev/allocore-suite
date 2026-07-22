<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\PlanHive\Models\Project;
use Modules\PlanHive\Models\Task;

class TaskController extends Controller
{
    public function index(Project $project): View
    {
        $tasks = $project->tasks()->with('assignee')->orderBy('position')->paginate(25);

        return view('planhive::tasks.index', compact('project', 'tasks'));
    }

    public function create(Project $project): View
    {
        return view('planhive::tasks.form', ['project' => $project, 'task' => new Task]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|string|in:todo,in_progress,done,cancelled',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'position' => 'nullable|integer',
        ]);

        $task = $project->tasks()->create($validated + ['team_id' => $project->team_id, 'user_id' => auth()->id()]);

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Task created.'));
    }

    public function show(Task $task): View
    {
        return view('planhive::tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        return view('planhive::tasks.form', ['project' => $task->project, 'task' => $task]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|string|in:todo,in_progress,done,cancelled',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'position' => 'nullable|integer',
        ]);

        $task->update($validated);

        return redirect()->route('planhive.projects.show', $task->project)->with('success', __('Task updated.'));
    }

    public function destroy(Task $task): RedirectResponse
    {
        $project = $task->project;
        $task->delete();

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Task deleted.'));
    }
}
