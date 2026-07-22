<?php

namespace Modules\VisionFlow\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\VisionFlow\Models\Organization;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $organizations = Organization::where('team_id', $request->user()->current_team_id)
            ->withCount(['values', 'principles', 'strategicGoals', 'missions', 'projects'])
            ->latest()
            ->get();

        return view('visionflow::organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        return view('visionflow::organizations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo_url' => ['nullable', 'url', 'max:255'],
        ]);

        $slug = Str::slug($validated['name']);
        if (Organization::where('slug', $slug)->exists()) {
            $slug .= '-'.uniqid();
        }

        $organization = Organization::create([
            ...$validated,
            'team_id' => $request->user()->current_team_id,
            'user_id' => $request->user()->id,
            'slug' => $slug,
        ]);

        ActivityLog::log('created', 'VisionFlow organization: '.$organization->name, $organization, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.show', $organization)->with('success', __('Organization created successfully.'));
    }

    public function show(Organization $organization): View
    {
        $this->authorizeOrg($organization);
        $organization->load(['values', 'principles.value', 'strategicGoals' => fn ($q) => $q->with('values'), 'visions', 'missions' => fn ($q) => $q->with('vision'), 'projects']);

        return view('visionflow::organizations.show', compact('organization'));
    }

    public function edit(Organization $organization): View
    {
        $this->authorizeOrg($organization);

        return view('visionflow::organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo_url' => ['nullable', 'url', 'max:255'],
        ]);

        $organization->update($validated);

        ActivityLog::log('updated', 'VisionFlow organization: '.$organization->name, $organization, $request->user(), ['team_id' => $organization->team_id]);

        return redirect()->route('visionflow.organizations.show', $organization)->with('success', __('Organization updated successfully.'));
    }

    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrg($organization);
        $name = $organization->name;
        $organization->delete();

        ActivityLog::log('deleted', 'VisionFlow organization: '.$name, null, $request->user(), ['team_id' => $request->user()->current_team_id]);

        return redirect()->route('visionflow.organizations.index')->with('success', __('Organization deleted successfully.'));
    }

    private function authorizeOrg(Organization $organization): void
    {
        abort_unless($organization->team_id === request()->user()?->current_team_id, 403);
    }
}
