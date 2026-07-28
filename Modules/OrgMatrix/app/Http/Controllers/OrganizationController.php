<?php

namespace Modules\OrgMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\OrgMatrix\Models\Organization;
use Modules\OrgMatrix\Models\Person;
use Modules\OrgMatrix\Models\Role;
use Modules\OrgMatrix\Models\RoleAssignment;

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

    public function seedDemo(Request $request): RedirectResponse
    {
        $organization = DB::transaction(function () use ($request) {
            $existing = Organization::where('user_id', $request->user()->id)
                ->where('name', 'Demo Organization')
                ->first();

            if ($existing) {
                return $existing;
            }

            $organization = Organization::create([
                'team_id' => $request->user()->current_team_id,
                'user_id' => $request->user()->id,
                'name' => 'Demo Organization',
                'description' => 'Sample organization to explore OrgMatrix features.',
                'industry' => 'Technology',
            ]);

            $roles = [
                ['name' => 'CEO', 'department' => 'Executive', 'criticality' => 'critical', 'parent' => null, 'sort_order' => 1],
                ['name' => 'CTO', 'department' => 'Technology', 'criticality' => 'high', 'parent' => 'CEO', 'sort_order' => 2],
                ['name' => 'HR Manager', 'department' => 'People', 'criticality' => 'medium', 'parent' => 'CEO', 'sort_order' => 3],
                ['name' => 'Developer', 'department' => 'Technology', 'criticality' => 'medium', 'parent' => 'CTO', 'sort_order' => 4],
                ['name' => 'Designer', 'department' => 'Design', 'criticality' => 'low', 'parent' => 'CTO', 'sort_order' => 5],
            ];

            $roleModels = [];
            foreach ($roles as $role) {
                $roleModels[$role['name']] = Role::create([
                    'team_id' => $organization->team_id,
                    'organization_id' => $organization->id,
                    'parent_role_id' => $role['parent'] ? $roleModels[$role['parent']]->id : null,
                    'name' => $role['name'],
                    'department' => $role['department'],
                    'criticality' => $role['criticality'],
                    'is_active' => true,
                    'sort_order' => $role['sort_order'],
                ]);
            }

            $people = [
                ['first_name' => 'Alice', 'last_name' => 'Smith', 'title' => 'Chief Executive Officer', 'department' => 'Executive'],
                ['first_name' => 'Bob', 'last_name' => 'Jones', 'title' => 'Chief Technology Officer', 'department' => 'Technology'],
                ['first_name' => 'Carol', 'last_name' => 'White', 'title' => 'HR Manager', 'department' => 'People'],
                ['first_name' => 'Dan', 'last_name' => 'Brown', 'title' => 'Senior Developer', 'department' => 'Technology'],
                ['first_name' => 'Eve', 'last_name' => 'Green', 'title' => 'Product Designer', 'department' => 'Design'],
            ];

            $personModels = [];
            foreach ($people as $person) {
                $personModels[] = Person::create([
                    'team_id' => $organization->team_id,
                    'organization_id' => $organization->id,
                    'first_name' => $person['first_name'],
                    'last_name' => $person['last_name'],
                    'title' => $person['title'],
                    'department' => $person['department'],
                    'is_active' => true,
                ]);
            }

            $assignments = [
                ['role' => 'CEO', 'person' => 0],
                ['role' => 'CTO', 'person' => 1],
                ['role' => 'HR Manager', 'person' => 2],
                ['role' => 'Developer', 'person' => 3],
                ['role' => 'Designer', 'person' => 4],
            ];

            foreach ($assignments as $assignment) {
                RoleAssignment::create([
                    'team_id' => $organization->team_id,
                    'role_id' => $roleModels[$assignment['role']]->id,
                    'person_id' => $personModels[$assignment['person']]->id,
                    'is_primary' => true,
                    'readiness_score' => 4,
                ]);
            }

            return $organization;
        });

        ActivityLog::log('created', 'Demo Organization: '.$organization->name, $organization, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.show', $organization)
            ->with('success', __('Demo organization created with sample roles, people and assignments.'));
    }

    private function authorizeOrganization(Organization $organization): void
    {
        abort_unless($organization->user_id === request()->user()?->id, 403);
    }
}
