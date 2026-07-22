<?php

namespace Modules\OrgMatrix\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\OrgMatrix\Models\Organization;
use Modules\OrgMatrix\Models\Person;

class PersonController extends Controller
{
    public function index(Organization $organization): View
    {
        $this->authorizeOrganization($organization);

        $people = $organization->people()
            ->with('roles')
            ->latest()
            ->get();

        return view('orgmatrix::people.index', compact('organization', 'people'));
    }

    public function create(Organization $organization): View
    {
        $this->authorizeOrganization($organization);

        return view('orgmatrix::people.create', compact('organization'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $person = $organization->people()->create([
            ...$validated,
            'team_id' => $organization->team_id,
        ]);

        ActivityLog::log('created', 'Person: '.$person->full_name, $person, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.people.index', $organization)
            ->with('success', __('Person added successfully.'));
    }

    public function edit(Organization $organization, Person $person): View
    {
        $this->authorizeOrganization($organization);

        $person->load('roles');

        return view('orgmatrix::people.edit', compact('organization', 'person'));
    }

    public function update(Request $request, Organization $organization, Person $person): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($person->avatar) {
                Storage::disk('public')->delete($person->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $person->update($validated);

        ActivityLog::log('updated', 'Person: '.$person->full_name, $person, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.people.index', $organization)
            ->with('success', __('Person updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization, Person $person): RedirectResponse
    {
        $this->authorizeOrganization($organization);

        $name = $person->full_name;
        $person->delete();

        ActivityLog::log('deleted', 'Person: '.$name, null, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('orgmatrix.organizations.people.index', $organization)
            ->with('success', __('Person removed successfully.'));
    }

    private function authorizeOrganization(Organization $organization): void
    {
        abort_unless($organization->user_id === request()->user()?->id, 403);
    }
}
