<?php

namespace Modules\VisionFlow\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VisionFlow\Models\Organization;
use Modules\VisionFlow\Models\Value;

class ValueController extends Controller
{
    private function authorizeOrg(Organization $organization): void
    {
        abort_unless($organization->team_id === request()->user()?->current_team_id, 403);
    }

    public function index(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $items = Value::where('organization_id', $organization->id)
            ->orderBy('sort_order')
            ->with('approver')
            ->get();

        return view('visionflow::values.index', compact('organization', 'items'));
    }

    public function create(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $values = $organization->values()->pluck('title', 'id');

        return view('visionflow::values.create', compact('organization', 'values'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,proposed,approved,archived'],
            'sort_order' => ['nullable', 'integer'],
        ]);
        $item = Value::create(array_merge($validated, ['organization_id' => $organization->id]));

        ActivityLog::log('created', 'Value created', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.values.index', $organization)->with('success', __('Created successfully.'));
    }

    public function show(Organization $organization, Value $item): View
    {
        $this->authorizeOrg($organization);
        $item->load('approver');

        return view('visionflow::values.show', compact('organization', 'item'));
    }

    public function edit(Organization $organization, Value $item): View
    {
        $this->authorizeOrg($organization);
        $values = $organization->values()->pluck('title', 'id');

        return view('visionflow::values.edit', compact('organization', 'item', 'values'));
    }

    public function update(Request $request, Organization $organization, Value $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,proposed,approved,archived'],
            'sort_order' => ['nullable', 'integer'],
        ]);
        $item->update($validated);

        ActivityLog::log('updated', 'Value updated', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.values.index', $organization)->with('success', __('Updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization, Value $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->delete();
        ActivityLog::log('deleted', 'Value deleted', null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.values.index', $organization)->with('success', __('Deleted successfully.'));
    }

    public function approve(Request $request, Organization $organization, Value $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $request->user()->id]);

        return redirect()->route('visionflow.organizations.values.index', $organization)->with('success', __('Value approved.'));
    }

    public function archive(Request $request, Organization $organization, Value $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->update(['status' => 'archived']);

        return redirect()->route('visionflow.organizations.values.index', $organization)->with('success', __('Value archived.'));
    }
}
