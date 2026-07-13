<?php

namespace Modules\LeadQuality\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\LeadQuality\Models\Contact;

class ActivityController
{
    public function store(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:outreach,follow-up,meeting,reminder',
            'notes' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);

        $contact->activities()->create([
            'user_id' => auth()->id(),
            'team_id' => auth()->user()->current_team_id,
            'type' => $validated['type'],
            'notes' => $validated['notes'],
            'scheduled_at' => $validated['scheduled_at'] ?? now(),
            'status' => Carbon::parse($validated['scheduled_at'] ?? now())->isFuture() ? 'pending' : 'completed',
        ]);

        $contact->update(['last_interaction_at' => now()]);

        return redirect()->back()->with('success', __('Activity logged successfully!'));
    }
}
