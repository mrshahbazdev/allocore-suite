<?php

namespace Modules\OrgMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\OrgMatrix\Models\Organization;

class ImportExportController extends Controller
{
    private function authorizeOrganization(Organization $organization): void
    {
        abort_unless($organization->user_id === request()->user()?->id, 403);
    }

    public function exportRoles(Organization $organization): Response
    {
        $this->authorizeOrganization($organization);

        $roles = $organization->roles()->with(['parent', 'assignments.person'])->orderBy('sort_order')->get();

        return response()->streamDownload(function () use ($roles) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Department', 'Reports To', 'Criticality', 'Active', 'Assigned People']);

            foreach ($roles as $role) {
                $assignees = $role->assignments->map(fn ($a) => $a->person->full_name.($a->is_primary ? ' (Primary)' : ''))->implode('; ');
                fputcsv($handle, [
                    $role->name,
                    $role->department ?? '',
                    $role->parent?->name ?? '',
                    $role->criticality,
                    $role->is_active ? 'Yes' : 'No',
                    $assignees,
                ]);
            }

            fclose($handle);
        }, $organization->name.'_roles.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportPeople(Organization $organization): Response
    {
        $this->authorizeOrganization($organization);

        $people = $organization->people()->with('roles')->latest()->get();

        return response()->streamDownload(function () use ($people) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['First Name', 'Last Name', 'Email', 'Phone', 'Job Title', 'Department', 'Roles']);

            foreach ($people as $person) {
                $roles = $person->roles->pluck('name')->implode('; ');
                fputcsv($handle, [
                    $person->first_name,
                    $person->last_name,
                    $person->email ?? '',
                    $person->phone ?? '',
                    $person->title ?? '',
                    $person->department ?? '',
                    $roles,
                ]);
            }

            fclose($handle);
        }, $organization->name.'_people.csv', ['Content-Type' => 'text/csv']);
    }

    public function importRoles(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $request->validate(['csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                continue;
            }

            $parentRole = null;
            if (! empty($row[2])) {
                $parentRole = $organization->roles()->where('name', $row[2])->first();
            }

            $organization->roles()->create([
                'name' => $row[0],
                'department' => $row[1] ?? null,
                'parent_role_id' => $parentRole?->id,
                'criticality' => in_array($row[3] ?? '', Role::CRITICALITIES) ? $row[3] : 'medium',
                'team_id' => $organization->team_id,
            ]);

            $imported++;
        }

        fclose($handle);

        ActivityLog::log('imported', $imported.' roles', $organization, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.roles.index', $organization)
            ->with('success', 'Successfully imported '.$imported.' roles.');
    }

    public function importPeople(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $request->validate(['csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                continue;
            }

            $organization->people()->create([
                'first_name' => $row[0],
                'last_name' => $row[1],
                'email' => $row[2] ?? null,
                'phone' => $row[3] ?? null,
                'title' => $row[4] ?? null,
                'department' => $row[5] ?? null,
                'team_id' => $organization->team_id,
            ]);

            $imported++;
        }

        fclose($handle);

        ActivityLog::log('imported', $imported.' people', $organization, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.people.index', $organization)
            ->with('success', 'Successfully imported '.$imported.' people.');
    }
}
