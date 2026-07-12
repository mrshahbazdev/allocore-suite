<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $teams = $request->user()->teams()->with('owner')->get();

        return view('teams.index', compact('teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);

        $team = Team::create(['name' => $validated['name'], 'owner_id' => $request->user()->id]);
        $team->members()->attach($request->user()->id, ['role' => 'owner']);
        $request->user()->update(['current_team_id' => $team->id]);

        return back()->with('success', __('Team created.'));
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
