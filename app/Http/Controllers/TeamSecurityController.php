<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamSecurityController extends Controller
{
    public function edit(Request $request, Team $team)
    {
        abort_if($team->owner_id !== $request->user()->id && ! $request->user()->isAdmin(), 403);

        return view('teams.security', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        abort_if($team->owner_id !== $request->user()->id && ! $request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'requires_two_factor' => ['nullable', 'boolean'],
        ]);

        $team->update([
            'requires_two_factor' => ! empty($validated['requires_two_factor']),
        ]);

        return redirect()->route('teams.security.edit', $team)->with('success', __('Team security settings updated.'));
    }
}
