<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamBrandingController extends Controller
{
    public function edit(Request $request, Team $team)
    {
        abort_unless($team->owner_id === $request->user()->id, 403);

        return view('teams.branding', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        abort_unless($team->owner_id === $request->user()->id, 403);

        $validated = $request->validate([
            'subdomain' => 'nullable|string|max:100|unique:teams,subdomain,'.$team->id,
            'custom_domain' => 'nullable|string|max:255|unique:teams,custom_domain,'.$team->id,
            'logo' => 'nullable|url|max:1000',
            'favicon' => 'nullable|url|max:1000',
            'primary_color' => 'nullable|hex_color|max:7',
            'accent_color' => 'nullable|hex_color|max:7',
        ]);

        $team->update($validated);

        return back()->with('success', __('Team branding updated.'));
    }
}
