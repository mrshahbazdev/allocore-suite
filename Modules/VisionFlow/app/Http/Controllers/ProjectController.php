<?php

namespace Modules\VisionFlow\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VisionFlow\Models\Organization;
use Modules\VisionFlow\Models\Project;

class ProjectController extends Controller
{
    private function authorizeOrg(Organization $organization): void
    {
        abort_unless($organization->team_id === request()->user()?->current_team_id, 403);
    }

    public function index(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $items = Project::where('organization_id', $organization->id)
            ->latest()
            ->with('mission')
            ->get();

        return view('visionflow::projects.index', compact('organization', 'items'));
    }

    public function create(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $missions = $organization->missions()->pluck('title', 'id');

        return view('visionflow::projects.create', compact('organization', 'missions'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'mission_id' => ['required', 'exists:visionflow_missions,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,on_hold,completed,archived'],
        ]);
        $item = Project::create(array_merge($validated, ['organization_id' => $organization->id]));

        ActivityLog::log('created', 'Project created', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.projects.index', $organization)->with('success', __('Created successfully.'));
    }

    public function show(Organization $organization, Project $item): View
    {
        $this->authorizeOrg($organization);
        $item->load('mission');

        return view('visionflow::projects.show', compact('organization', 'item'));
    }

    public function edit(Organization $organization, Project $item): View
    {
        $this->authorizeOrg($organization);
        $missions = $organization->missions()->pluck('title', 'id');

        return view('visionflow::projects.edit', compact('organization', 'item', 'missions'));
    }

    public function update(Request $request, Organization $organization, Project $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'mission_id' => ['required', 'exists:visionflow_missions,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,on_hold,completed,archived'],
        ]);
        $item->update($validated);

        ActivityLog::log('updated', 'Project updated', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.projects.index', $organization)->with('success', __('Updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization, Project $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->delete();
        ActivityLog::log('deleted', 'Project deleted', null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.projects.index', $organization)->with('success', __('Deleted successfully.'));
    }
}
