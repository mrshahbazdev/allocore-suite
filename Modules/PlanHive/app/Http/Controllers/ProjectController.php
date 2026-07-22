<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\PlanHive\Models\Project;
use Modules\PlanHive\Models\ProjectMember;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::query()->with(['owner'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        return view('planhive::projects.index', [
            'projects' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('planhive::projects.form', ['project' => new Project]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'status' => 'nullable|string|in:active,archived,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = Project::create($validated);
        $project->members()->attach(auth()->id(), ['role' => 'owner']);

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Project created.'));
    }

    public function show(Project $project): View
    {
        $project->load(['owner', 'members', 'tasks', 'goals', 'calendarEvents', 'notes', 'documents', 'contacts']);

        return view('planhive::projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        return view('planhive::projects.form', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'status' => 'nullable|string|in:active,archived,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project->update($validated);

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Project updated.'));
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('planhive.projects.index')->with('success', __('Project deleted.'));
    }

    public function addMember(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|string|in:member,manager,boss',
        ]);

        $user = User::query()->where('email', $validated['email'])->firstOrFail();

        ProjectMember::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $user->id],
            ['role' => $validated['role']]
        );

        return back()->with('success', __('Member added.'));
    }

    public function removeMember(Project $project, User $user): RedirectResponse
    {
        ProjectMember::query()->where('project_id', $project->id)->where('user_id', $user->id)->delete();

        return back()->with('success', __('Member removed.'));
    }
}
