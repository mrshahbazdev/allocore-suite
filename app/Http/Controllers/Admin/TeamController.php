<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $teams = Team::query()
            ->withCount(['members', 'toolSubscriptions'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')
                    ->orWhereHas('owner', fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.teams.index', compact('teams'));
    }

    public function show(Team $team)
    {
        $team->load(['owner', 'members', 'toolSubscriptions.plan.modules', 'ownedTeams']);

        return view('admin.teams.show', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
        ]);

        $team->update($validated);

        return back()->with('success', __('Team updated.'));
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()->route('admin.teams.index')->with('success', __('Team deleted.'));
    }

    public function removeMember(Team $team, User $user)
    {
        if ($team->owner_id === $user->id) {
            return back()->with('error', __('Cannot remove the team owner.'));
        }

        $team->members()->detach($user);

        return back()->with('success', __('Member removed from team.'));
    }
}
