<?php

namespace Modules\LeadQuality\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LeadQuality\Models\IcpProfile;

class IcpProfileController
{
    public function index(): View
    {
        $profile = IcpProfile::query()->first() ?? new IcpProfile;

        return view('leadquality::icp.index', compact('profile'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'industry' => 'nullable|string|max:255',
            'employee_count_range' => 'nullable|string|max:255',
            'budget_min' => 'nullable|numeric',
            'budget_max' => 'nullable|numeric',
            'role' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        IcpProfile::updateOrCreate(
            ['team_id' => auth()->user()->current_team_id],
            $validated + [
                'team_id' => auth()->user()->current_team_id,
                'user_id' => auth()->id(),
            ]
        );

        return redirect()->route('leadquality.icp.index')->with('success', __('ICP Profile updated!'));
    }
}
