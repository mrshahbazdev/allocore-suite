<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\PlanHive\Models\Goal;
use Modules\PlanHive\Models\Note;
use Modules\PlanHive\Models\Project;
use Modules\PlanHive\Models\Reminder;
use Modules\PlanHive\Models\Task;

class ReminderController extends Controller
{
    public function index(): View
    {
        $reminders = Reminder::query()
            ->where('user_id', auth()->id())
            ->orWhereIn('project_id', function ($query): void {
                $query->select('project_id')
                    ->from('planhive_project_members')
                    ->where('user_id', auth()->id());
            })
            ->with('remindable')
            ->orderBy('remind_at')
            ->paginate(25);

        return view('planhive::reminders.index', compact('reminders'));
    }

    public function create(Project $project): View
    {
        return view('planhive::reminders.form', ['project' => $project, 'reminder' => new Reminder]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'remind_at' => 'required|date',
            'remindable_type' => 'required|string|in:task,goal,note,project',
            'remindable_id' => 'required|integer',
        ]);

        $typeMap = [
            'task' => Task::class,
            'goal' => Goal::class,
            'note' => Note::class,
            'project' => Project::class,
        ];

        $validated['remindable_type'] = $typeMap[$validated['remindable_type']] ?? Project::class;

        $project->reminders()->create($validated + ['team_id' => $project->team_id, 'user_id' => auth()->id()]);

        return redirect()->route('planhive.reminders.index', $project)->with('success', __('Reminder created.'));
    }

    public function edit(Reminder $reminder): View
    {
        return view('planhive::reminders.form', ['project' => $reminder->project, 'reminder' => $reminder]);
    }

    public function update(Request $request, Reminder $reminder): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'remind_at' => 'required|date',
            'is_done' => 'nullable|boolean',
        ]);

        $validated['is_done'] = $request->boolean('is_done');
        $reminder->update($validated);

        return redirect()->route('planhive.reminders.index', $reminder->project)->with('success', __('Reminder updated.'));
    }

    public function destroy(Reminder $reminder): RedirectResponse
    {
        $reminder->delete();

        return redirect()->route('planhive.reminders.index', $reminder->project)->with('success', __('Reminder deleted.'));
    }
}
