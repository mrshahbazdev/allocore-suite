<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\PlanHive\Models\Note;
use Modules\PlanHive\Models\Project;

class NoteController extends Controller
{
    public function index(Project $project): View
    {
        $notes = $project->notes()->latest()->paginate(25);

        return view('planhive::notes.index', compact('project', 'notes'));
    }

    public function show(Note $note): View
    {
        return view('planhive::notes.show', compact('note'));
    }

    public function create(Project $project): View
    {
        return view('planhive::notes.form', ['project' => $project, 'note' => new Note]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $project->notes()->create($validated + ['team_id' => $project->team_id, 'user_id' => auth()->id()]);

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Note created.'));
    }

    public function edit(Note $note): View
    {
        return view('planhive::notes.form', ['project' => $note->project, 'note' => $note]);
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $note->update($validated);

        return redirect()->route('planhive.notes.show', $note)->with('success', __('Note updated.'));
    }

    public function destroy(Note $note): RedirectResponse
    {
        $project = $note->project;
        $note->delete();

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Note deleted.'));
    }
}
