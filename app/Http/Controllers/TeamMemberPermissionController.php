<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamMemberPermissionController extends Controller
{
    public function edit(Request $request, Team $team, User $member)
    {
        abort_unless($team->owner_id === $request->user()->id, 403);
        abort_unless($team->members()->where('users.id', $member->id)->exists(), 403);

        $modules = Module::where('is_active', true)->orderBy('name')->get();
        $pivot = $team->members()->where('users.id', $member->id)->first()->pivot;
        $allowed = json_decode($pivot->allowed_modules, true) ?: [];

        return view('teams.members.permissions', compact('team', 'member', 'modules', 'allowed'));
    }

    public function update(Request $request, Team $team, User $member)
    {
        abort_unless($team->owner_id === $request->user()->id, 403);
        abort_unless($team->members()->where('users.id', $member->id)->exists(), 403);

        $validated = $request->validate([
            'allowed_modules' => 'nullable|array',
            'allowed_modules.*' => 'string|exists:modules,key',
        ]);

        $team->members()->updateExistingPivot($member->id, [
            'allowed_modules' => json_encode($validated['allowed_modules'] ?? []),
        ]);

        return back()->with('success', __('Member permissions updated.'));
    }
}
