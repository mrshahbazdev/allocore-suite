<?php

namespace Modules\VisionFlow\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VisionFlow\Models\Organization;
use Modules\VisionFlow\Models\Principle;

class PrincipleController extends Controller
{
    private function authorizeOrg(Organization $organization): void
    {
        abort_unless($organization->team_id === request()->user()?->current_team_id, 403);
    }

    public function index(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $items = Principle::where('organization_id', $organization->id)
            ->latest()
            ->with('value')
            ->get();

        return view('visionflow::principles.index', compact('organization', 'items'));
    }

    public function create(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $values = $organization->values()->where('status', 'approved')->pluck('title', 'id');

        return view('visionflow::principles.create', compact('organization', 'values'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'value_id' => ['required', 'exists:visionflow_values,id'],
            'statement' => ['required', 'string'],
            'status' => ['required', 'in:draft,proposed,approved'],
            'alignment_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        $item = Principle::create(array_merge($validated, ['organization_id' => $organization->id]));

        ActivityLog::log('created', 'Principle created', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.principles.index', $organization)->with('success', __('Created successfully.'));
    }

    public function show(Organization $organization, Principle $item): View
    {
        $this->authorizeOrg($organization);
        $item->load('value');

        return view('visionflow::principles.show', compact('organization', 'item'));
    }

    public function edit(Organization $organization, Principle $item): View
    {
        $this->authorizeOrg($organization);
        $values = $organization->values()->where('status', 'approved')->pluck('title', 'id');

        return view('visionflow::principles.edit', compact('organization', 'item', 'values'));
    }

    public function update(Request $request, Organization $organization, Principle $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'value_id' => ['required', 'exists:visionflow_values,id'],
            'statement' => ['required', 'string'],
            'status' => ['required', 'in:draft,proposed,approved'],
            'alignment_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        $item->update($validated);

        ActivityLog::log('updated', 'Principle updated', $item, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.principles.index', $organization)->with('success', __('Updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization, Principle $item): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $item->delete();
        ActivityLog::log('deleted', 'Principle deleted', null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.principles.index', $organization)->with('success', __('Deleted successfully.'));
    }
}
