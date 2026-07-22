<?php

namespace Modules\VisionFlow\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VisionFlow\Models\Mission;
use Modules\VisionFlow\Models\Organization;

class MissionController extends Controller
{
    private function authorizeOrg(Organization $organization): void
    {
        abort_unless($organization->team_id === request()->user()?->current_team_id, 403);
    }

    public function index(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $items = Mission::where('organization_id', $organization->id)
            ->latest()
            ->with(['vision', 'owner'])
            ->get();

        return view('visionflow::missions.index', compact('organization', 'items'));
    }

    public function create(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $visions = $organization->visions()
            ->where('status', 'approved')
            ->orWhere('is_current', true)
            ->pluck('content', 'id');
        $users = User::all()->pluck('name', 'id');

        return view('visionflow::missions.create', compact('organization', 'visions', 'users'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'vision_id' => ['required', 'exists:visionflow_visions,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:active,paused,completed,archived'],
            'review_cadence' => ['nullable', 'in:monthly,quarterly,biannually,annually'],
            'next_review_at' => ['nullable', 'date'],
        ]);
        $item = Mission::create(array_merge($validated, ['organization_id' => $organization->id]));

        ActivityLog::log('created', 'Mission created', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.missions.index', $organization)->with('success', __('Created successfully.'));
    }

    public function show(Organization $organization, Mission $item): View
    {
        $this->authorizeOrg($organization);
        $item->load('vision', 'owner', 'projects');

        return view('visionflow::missions.show', compact('organization', 'item'));
    }

    public function edit(Organization $organization, Mission $item): View
    {
        $this->authorizeOrg($organization);
        $visions = $organization->visions()->pluck('content', 'id');
        $users = User::all()->pluck('name', 'id');

        return view('visionflow::missions.edit', compact('organization', 'item', 'visions', 'users'));
    }

    public function update(Request $request, Organization $organization, Mission $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'vision_id' => ['required', 'exists:visionflow_visions,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:active,paused,completed,archived'],
            'review_cadence' => ['nullable', 'in:monthly,quarterly,biannually,annually'],
            'next_review_at' => ['nullable', 'date'],
        ]);
        $item->update($validated);

        ActivityLog::log('updated', 'Mission updated', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.missions.index', $organization)->with('success', __('Updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization, Mission $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->delete();

        ActivityLog::log('deleted', 'Mission deleted', null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.missions.index', $organization)->with('success', __('Deleted successfully.'));
    }
}
