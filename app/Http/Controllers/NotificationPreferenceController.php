<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationPreferenceController extends Controller
{
    public function index(Request $request)
    {
        $types = ['general', 'alerts'];
        $preferences = collect($types)->mapWithKeys(function ($type) use ($request) {
            return [$type => $request->user()->notificationPreference($type)];
        });

        return view('notifications.preferences', compact('preferences'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*.email' => 'boolean',
            'preferences.*.in_app' => 'boolean',
            'preferences.*.slack' => 'boolean',
            'preferences.*.slack_webhook' => 'nullable|url|max:1000',
        ]);

        foreach ($validated['preferences'] as $type => $data) {
            $preference = $request->user()->notificationPreference($type);
            $preference->update([
                'email' => $data['email'] ?? false,
                'in_app' => $data['in_app'] ?? false,
                'slack' => $data['slack'] ?? false,
                'slack_webhook' => $data['slack_webhook'] ?? null,
            ]);
        }

        return back()->with('success', __('Notification preferences saved.'));
    }
}
