<?php

namespace Modules\OrgMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\OrgMatrix\Models\Organization;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $organizations = Organization::where('user_id', $request->user()->id)
            ->withCount(['roles', 'people'])
            ->latest()
            ->get();

        return view('orgmatrix::organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        return view('orgmatrix::organizations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'industry' => ['nullable', 'string', 'max:255'],
        ]);

        $organization = Organization::create([
            ...$validated,
            'team_id' => $request->user()->current_team_id,
            'user_id' => $request->user()->id,
        ]);

        ActivityLog::log('created', 'Organization: '.$organization->name, $organization, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.show', $organization)
            ->with('success', __('Organization created successfully.'));
    }

    public function show(Organization $organization): View
    {
        $this->authorizeOrganization($organization);

        $organization->load([
            'roles' => fn ($q) => $q->withCount('assignments'),
            'people' => fn ($q) => $q->withCount('assignments'),
        ]);

        $stats = [
            'total_roles' => $organization->roles->count(),
            'total_people' => $organization->people->count(),
            'active_roles' => $organization->roles->where('is_active', true)->count(),
            'unassigned_roles' => $organization->roles->filter(fn ($r) => $r->assignments_count === 0)->count(),
        ];

        return view('orgmatrix::organizations.show', compact('organization', 'stats'));
    }

    public function edit(Organization $organization): View
    {
        $this->authorizeOrganization($organization);

        return view('orgmatrix::organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'industry' => ['nullable', 'string', 'max:255'],
        ]);

        $organization->update($validated);

        ActivityLog::log('updated', 'Organization: '.$organization->name, $organization, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.show', $organization)
            ->with('success', __('Organization updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $name = $organization->name;
        $organization->delete();

        ActivityLog::log('deleted', 'Organization: '.$name, null, $request->user(), ['team_id' => $request->user()->current_team_id]);

        return redirect()->route('orgmatrix.organizations.index')
            ->with('success', __('Organization deleted successfully.'));
    }

    private function authorizeOrganization(Organization $organization): void
    {
        abort_unless($organization->user_id === request()->user()?->id, 403);
    }
}
