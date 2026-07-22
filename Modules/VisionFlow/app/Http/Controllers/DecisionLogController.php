<?php

namespace Modules\VisionFlow\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VisionFlow\Models\DecisionLog;
use Modules\VisionFlow\Models\Organization;

class DecisionLogController extends Controller
{
    private function authorizeOrg(Organization $organization): void
    {
        abort_unless($organization->team_id === request()->user()?->current_team_id, 403);
    }

    public function index(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $items = DecisionLog::where('organization_id', $organization->id)
            ->latest()
            ->with(['value', 'mission', 'user'])
            ->get();

        return view('visionflow::decision-logs.index', compact('organization', 'items'));
    }

    public function create(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $values = $organization->values()->pluck('title', 'id');
        $missions = $organization->missions()->pluck('title', 'id');

        return view('visionflow::decision-logs.create', compact('organization', 'values', 'missions'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'decision' => ['required', 'string'],
            'supporting_value_id' => ['nullable', 'exists:visionflow_values,id'],
            'supporting_mission_id' => ['nullable', 'exists:visionflow_missions,id'],
        ]);
        $item = DecisionLog::create(array_merge($validated, ['organization_id' => $organization->id, 'user_id' => auth()->id()]));

        ActivityLog::log('created', 'DecisionLog created', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.decision-logs.index', $organization)->with('success', __('Created successfully.'));
    }

    public function show(Organization $organization, DecisionLog $item): View
    {
        $this->authorizeOrg($organization);
        $item->load('value', 'mission', 'user');

        return view('visionflow::decision-logs.show', compact('organization', 'item'));
    }

    public function edit(Organization $organization, DecisionLog $item): View
    {
        $this->authorizeOrg($organization);
        $values = $organization->values()->pluck('title', 'id');
        $missions = $organization->missions()->pluck('title', 'id');

        return view('visionflow::decision-logs.edit', compact('organization', 'item', 'values', 'missions'));
    }

    public function update(Request $request, Organization $organization, DecisionLog $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'decision' => ['required', 'string'],
            'supporting_value_id' => ['nullable', 'exists:visionflow_values,id'],
            'supporting_mission_id' => ['nullable', 'exists:visionflow_missions,id'],
        ]);
        $item->update($validated);

        ActivityLog::log('updated', 'DecisionLog updated', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.decision-logs.index', $organization)->with('success', __('Updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization, DecisionLog $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->delete();
        ActivityLog::log('deleted', 'DecisionLog deleted', null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.decision-logs.index', $organization)->with('success', __('Deleted successfully.'));
    }
}
