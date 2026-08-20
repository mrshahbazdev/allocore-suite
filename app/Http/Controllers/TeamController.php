<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use App\Models\Team;
use App\Models\User;
use App\Services\MaturityDataSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $teams = $request->user()->teams()->with('owner')->get();
        $industryClusters = Industry::clusters()->with('children')->get();

        return view('teams.index', compact('teams', 'industryClusters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'industry' => 'required|string|max:255',
            'industry_sub' => 'nullable|string|max:255',
            'size' => 'required|string|max:100',
            'company_age' => 'nullable|integer|min:0|max:250',
            'country' => 'required|string|max:100',
            'revenue_range' => 'required|string|max:100',
        ]);

        $team = Team::create($validated + ['owner_id' => $request->user()->id]);
        $team->members()->attach($request->user()->id, ['role' => 'owner']);
        $request->user()->update(['current_team_id' => $team->id]);

        return back()->with('success', __('Team created.'));
    }

    public function update(Request $request, Team $team)
    {
        abort_unless($team->owner_id === $request->user()->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'industry' => 'required|string|max:255',
            'industry_sub' => 'nullable|string|max:255',
            'size' => 'required|string|max:100',
            'company_age' => 'nullable|integer|min:0|max:250',
            'country' => 'required|string|max:100',
            'revenue_range' => 'required|string|max:100',
        ]);

        $team->update($validated);

        MaturityDataSnapshotService::syncForTeam($team);

        return back()->with('success', __('Team profile updated.'));
    }

    public function switch(Request $request, Team $team)
    {
        abort_unless($request->user()->teams()->where('teams.id', $team->id)->exists(), 403);

        $request->user()->update(['current_team_id' => $team->id]);

        return back()->with('success', __('Switched team.'));
    }

    public function addMember(Request $request, Team $team)
    {
        abort_unless($team->owner_id === $request->user()->id, 403);

        $validated = $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $validated['email'])->first();
        $team->members()->syncWithoutDetaching([$user->id => ['role' => 'member']]);

        return back()->with('success', __('Member added.'));
    }
}
