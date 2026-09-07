<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\CompetencyRole;
use Modules\BookIntelligence\Models\UserExpertiseProfile;
use Modules\BookIntelligence\Services\ExpertiseProgressionService;

class ExpertiseController extends Controller
{
    public function index(ExpertiseProgressionService $service): View
    {
        $user = auth()->user();
        $profile = $service->getProfile($user);
        $roles = CompetencyRole::query()->orderBy('name')->get();
        $teamProfiles = UserExpertiseProfile::query()->with(['user', 'currentRole', 'targetRole'])->orderByDesc('points')->get();

        $levels = [
            ['name' => 'Starter', 'key' => 'starter', 'min_points' => 0, 'color' => 'slate'],
            ['name' => 'Junior', 'key' => 'junior', 'min_points' => 250, 'color' => 'blue'],
            ['name' => 'Professional', 'key' => 'professional', 'min_points' => 750, 'color' => 'emerald'],
            ['name' => 'Senior', 'key' => 'senior', 'min_points' => 1500, 'color' => 'purple'],
            ['name' => 'Expert', 'key' => 'expert', 'min_points' => 3000, 'color' => 'amber'],
            ['name' => 'Master', 'key' => 'master', 'min_points' => 5000, 'color' => 'rose'],
        ];

        return view('bookintelligence::expertise.index', compact('profile', 'roles', 'teamProfiles', 'levels'));
    }

    public function updateRoles(Request $request, ExpertiseProgressionService $service): RedirectResponse
    {
        $validated = $request->validate([
            'current_role_id' => ['nullable', 'exists:bookintelligence_competency_roles,id'],
            'target_role_id' => ['nullable', 'exists:bookintelligence_competency_roles,id'],
        ]);

        $profile = $service->getProfile(auth()->user());
        $profile->update($validated);

        return redirect()->route('bookintelligence.expertise.index')
            ->with('status', 'Career targets and expertise role updated.');
    }
}
