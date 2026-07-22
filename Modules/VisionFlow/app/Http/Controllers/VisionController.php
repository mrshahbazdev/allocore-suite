<?php

namespace Modules\VisionFlow\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VisionFlow\Models\Organization;
use Modules\VisionFlow\Models\Vision;

class VisionController extends Controller
{
    private function authorizeOrg(Organization $organization): void
    {
        abort_unless($organization->team_id === request()->user()?->current_team_id, 403);
    }

    public function index(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $items = Vision::where('organization_id', $organization->id)
            ->latest()
            ->with('approver')
            ->get();

        return view('visionflow::visions.index', compact('organization', 'items'));
    }

    public function create(Organization $organization): View
    {
        $this->authorizeOrg($organization);

        return view('visionflow::visions.create', compact('organization'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'status' => ['required', 'in:drafting,reviewing,approved'],
            'version' => ['nullable', 'integer'],
        ]);
        $item = Vision::create(array_merge($validated, ['organization_id' => $organization->id]));

        ActivityLog::log('created', 'Vision created', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.visions.index', $organization)->with('success', __('Created successfully.'));
    }

    public function show(Organization $organization, Vision $item): View
    {
        $this->authorizeOrg($organization);
        $item->load('missions', 'approver');

        return view('visionflow::visions.show', compact('organization', 'item'));
    }

    public function edit(Organization $organization, Vision $item): View
    {
        $this->authorizeOrg($organization);

        return view('visionflow::visions.edit', compact('organization', 'item'));
    }

    public function update(Request $request, Organization $organization, Vision $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'status' => ['required', 'in:drafting,reviewing,approved'],
            'version' => ['nullable', 'integer'],
        ]);
        $item->update($validated);

        ActivityLog::log('updated', 'Vision updated', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.visions.index', $organization)->with('success', __('Updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization, Vision $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->delete();
        ActivityLog::log('deleted', 'Vision deleted', null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.visions.index', $organization)->with('success', __('Deleted successfully.'));
    }

    public function approve(Request $request, Organization $organization, Vision $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $request->user()->id]);

        return redirect()->route('visionflow.organizations.visions.index', $organization)->with('success', __('Vision approved.'));
    }

    public function setCurrent(Request $request, Organization $organization, Vision $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $organization->visions()->update(['is_current' => false]);
        $item->update(['is_current' => true]);

        return redirect()->route('visionflow.organizations.visions.index', $organization)->with('success', __('Vision set as current.'));
    }
}
