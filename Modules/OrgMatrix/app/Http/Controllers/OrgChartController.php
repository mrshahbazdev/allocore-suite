<?php

namespace Modules\OrgMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\OrgMatrix\Models\Organization;

class OrgChartController extends Controller
{
    public function index(Organization $organization): View
    {
        abort_unless($organization->user_id === request()->user()?->id, 403);

        $roles = $organization->roles()
            ->with(['assignments.person', 'parent'])
            ->orderBy('sort_order')
            ->get();

        $tree = $this->buildTree($roles);

        return view('orgmatrix::org-chart.index', compact('organization', 'tree'));
    }

    private function buildTree($roles, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($roles->where('parent_role_id', $parentId) as $role) {
            $primaryAssignment = $role->assignments->firstWhere('is_primary', true);
            $person = $primaryAssignment ? $primaryAssignment->person : null;

            $successors = $role->assignments
                ->filter(fn ($a) => $a->succession_horizon !== null)
                ->map(fn ($a) => [
                    'person_name' => $a->person?->full_name,
                    'horizon' => $a->succession_horizon,
                    'readiness' => $a->readiness_score,
                ]);

            $hasBackup = $role->assignments->count() > 1
                || $role->assignments->where('succession_horizon', '!=', null)->isNotEmpty();

            $tree[] = [
                'id' => $role->id,
                'role_id' => $role->id,
                'name' => $role->name,
                'department' => $role->department,
                'criticality' => $role->criticality,
                'is_active' => $role->is_active,
                'person' => $person ? [
                    'id' => $person->id,
                    'name' => $person->full_name,
                    'title' => $person->title,
                    'avatar' => $person->avatar,
                ] : null,
                'assignee_count' => $role->assignments->count(),
                'has_backup' => $hasBackup,
                'successors' => $successors->values(),
                'children' => $this->buildTree($roles, $role->id),
            ];
        }

        return $tree;
    }
}
