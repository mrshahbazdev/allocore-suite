<?php

namespace Modules\VisionFlow\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VisionFlow\Models\Organization;
use Modules\VisionFlow\Models\StrategicGoal;

class StrategicGoalController extends Controller
{
    private function authorizeOrg(Organization $organization): void
    {
        abort_unless($organization->team_id === request()->user()?->current_team_id, 403);
    }

    public function index(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $items = StrategicGoal::where('organization_id', $organization->id)
            ->orderBy('category')
            ->with(['values', 'principles'])
            ->get();

        return view('visionflow::strategic-goals.index', compact('organization', 'items'));
    }

    public function create(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $values = $organization->values()->pluck('title', 'id');
        $principles = $organization->principles()->pluck('statement', 'id');

        return view('visionflow::strategic-goals.create', compact('organization', 'values', 'principles'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:market,impact,organization'],
            'time_horizon' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,archived'],
            'values' => ['nullable', 'array'],
            'values.*' => ['exists:visionflow_values,id'],
            'principles' => ['nullable', 'array'],
            'principles.*' => ['exists:visionflow_principles,id'],
        ]);
        $item = StrategicGoal::create(array_merge($validated, ['organization_id' => $organization->id]));
        $item->values()->sync($validated['values'] ?? []);
        $item->principles()->sync($validated['principles'] ?? []);
        ActivityLog::log('created', 'StrategicGoal created', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.strategic-goals.index', $organization)->with('success', __('Created successfully.'));
    }

    public function show(Organization $organization, StrategicGoal $item): View
    {
        $this->authorizeOrg($organization);
        $item->load('values', 'principles');

        return view('visionflow::strategic-goals.show', compact('organization', 'item'));
    }

    public function edit(Organization $organization, StrategicGoal $item): View
    {
        $this->authorizeOrg($organization);
        $values = $organization->values()->pluck('title', 'id');
        $principles = $organization->principles()->pluck('statement', 'id');

        return view('visionflow::strategic-goals.edit', compact('organization', 'item', 'values', 'principles'));
    }

    public function update(Request $request, Organization $organization, StrategicGoal $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:market,impact,organization'],
            'time_horizon' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,archived'],
            'values' => ['nullable', 'array'],
            'values.*' => ['exists:visionflow_values,id'],
            'principles' => ['nullable', 'array'],
            'principles.*' => ['exists:visionflow_principles,id'],
        ]);
        $item->update($validated);
        $item->values()->sync($validated['values'] ?? []);
        $item->principles()->sync($validated['principles'] ?? []);
        ActivityLog::log('updated', 'StrategicGoal updated', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.strategic-goals.index', $organization)->with('success', __('Updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization, StrategicGoal $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->delete();
        ActivityLog::log('deleted', 'StrategicGoal deleted', null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.strategic-goals.index', $organization)->with('success', __('Deleted successfully.'));
    }
}
