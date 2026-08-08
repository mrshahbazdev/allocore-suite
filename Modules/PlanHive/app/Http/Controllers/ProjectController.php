<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Modules\PlanHive\Mail\ProjectMemberAdded;
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

        $validated['color'] ??= '#6366f1';
        $validated['status'] ??= 'active';

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

        $validated['color'] ??= $project->color ?? '#6366f1';
        $validated['status'] ??= $project->status ?? 'active';

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
            'email' => 'required|email',
            'role' => 'required|string|in:member,manager,boss',
        ]);

        $email = $validated['email'];
        $user = User::query()->where('email', $email)->first();

        if ($user && $user->teams()->where('teams.id', $project->team_id)->exists()) {
            ProjectMember::updateOrCreate(
                ['project_id' => $project->id, 'user_id' => $user->id],
                ['role' => $validated['role']]
            );

            try {
                Mail::to($email)->send(new ProjectMemberAdded($project, $validated['role']));
            } catch (\Throwable $e) {
                report($e);
            }

            return back()->with('success', __('Member added and notified.'));
        }

        $invitation = TeamInvitation::updateOrCreate(
            [
                'team_id' => $project->team_id,
                'project_id' => $project->id,
                'email' => $email,
                'accepted_at' => null,
            ],
            [
                'invited_by' => $request->user()->id,
                'role' => 'member',
                'project_role' => $validated['role'],
                'token' => TeamInvitation::generateToken(),
                'expires_at' => Carbon::now()->addDays(7),
            ]
        );

        try {
            Mail::to($email)->send(new TeamInvitationMail($invitation));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', __('Invitation sent to :email', ['email' => $email]));
    }

    public function removeMember(Project $project, User $user): RedirectResponse
    {
        ProjectMember::query()->where('project_id', $project->id)->where('user_id', $user->id)->delete();

        return back()->with('success', __('Member removed.'));
    }
}
