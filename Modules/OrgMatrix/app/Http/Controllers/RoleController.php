<?php

namespace Modules\OrgMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\OrgMatrix\Models\Organization;
use Modules\OrgMatrix\Models\Role;

class RoleController extends Controller
{
    public function index(Organization $organization): View
    {
        $this->authorizeOrganization($organization);

        $roles = $organization->roles()
            ->with(['parent', 'assignments.person'])
            ->orderBy('sort_order')
            ->get();

        return view('orgmatrix::roles.index', compact('organization', 'roles'));
    }

    public function create(Organization $organization): View
    {
        $this->authorizeOrganization($organization);

        $parentRoles = $organization->roles()->select('id', 'name')->get();

        return view('orgmatrix::roles.create', compact('organization', 'parentRoles'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'department' => ['nullable', 'string', 'max:255'],
            'parent_role_id' => ['nullable', 'exists:orgmatrix_roles,id'],
            'criticality' => ['required', 'in:low,medium,high,critical'],
        ]);

        $role = $organization->roles()->create([
            ...$validated,
            'team_id' => $organization->team_id,
        ]);

        ActivityLog::log('created', 'Role: '.$role->name, $role, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.roles.index', $organization)
            ->with('success', __('Role created successfully.'));
    }

    public function edit(Organization $organization, Role $role): View
    {
        $this->authorizeOrganization($organization);

        $parentRoles = $organization->roles()
            ->where('id', '!=', $role->id)
            ->select('id', 'name')
            ->get();

        return view('orgmatrix::roles.edit', compact('organization', 'role', 'parentRoles'));
    }

    public function update(Request $request, Organization $organization, Role $role): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'department' => ['nullable', 'string', 'max:255'],
            'parent_role_id' => ['nullable', 'exists:orgmatrix_roles,id'],
            'criticality' => ['required', 'in:low,medium,high,critical'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $role->update($validated);

        ActivityLog::log('updated', 'Role: '.$role->name, $role, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.roles.index', $organization)
            ->with('success', __('Role updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization, Role $role): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $name = $role->name;
        $role->delete();

        ActivityLog::log('deleted', 'Role: '.$name, null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.roles.index', $organization)
            ->with('success', __('Role deleted successfully.'));
    }

    private function authorizeOrganization(Organization $organization): void
    {
        abort_unless($organization->user_id === request()->user()?->id, 403);
    }
}
