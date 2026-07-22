<?php

namespace Modules\OrgMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\OrgMatrix\Models\Organization;
use Modules\OrgMatrix\Models\Role;
use Modules\OrgMatrix\Models\RoleAssignment;

class RoleAssignmentController extends Controller
{
    public function create(Organization $organization, Role $role): View
    {
        $this->authorizeOrganization($organization);

        $availablePeople = $organization->people()
            ->whereNotIn('id', $role->assignments()->pluck('person_id'))
            ->get();

        return view('orgmatrix::assignments.create', compact('organization', 'role', 'availablePeople'));
    }

    public function store(Request $request, Organization $organization, Role $role): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $validated = $request->validate([
            'person_id' => ['required', 'exists:orgmatrix_people,id'],
            'is_primary' => ['boolean'],
            'succession_horizon' => ['nullable', 'in:short,mid,long'],
            'readiness_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['is_primary'] ?? false) {
            $role->assignments()->update(['is_primary' => false]);
        }

        $assignment = $role->assignments()->create([
            ...$validated,
            'team_id' => $organization->team_id,
        ]);

        ActivityLog::log(
            'assigned',
            $assignment->person->full_name.' assigned to '.$role->name,
            $assignment,
            $request->user(),
            ['team_id' => $organization->team_id]
        );

        return redirect()->route('orgmatrix.organizations.roles.index', $organization)
            ->with('success', __('Person assigned to role successfully.'));
    }

    public function destroy(Request $request, Organization $organization, Role $role, RoleAssignment $assignment): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $desc = ($assignment->person->full_name ?? '').' -> '.($role->name ?? '');
        $assignment->delete();

        ActivityLog::log('unassigned', $desc, null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.roles.index', $organization)
            ->with('success', __('Assignment removed successfully.'));
    }

    private function authorizeOrganization(Organization $organization): void
    {
        abort_unless($organization->user_id === request()->user()?->id, 403);
    }
}
