<?php

namespace Modules\BunnyBand\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\Setting;

class SettingController extends Controller
{
    public function index(): View
    {
        $teamId = auth()->user()->current_team_id;

        $settings = [
            'welcome_bonus' => (float) Setting::get($teamId, 'welcome_bonus', 10),
            'referral_reward' => (float) Setting::get($teamId, 'referral_reward', 5),
            'task_reward' => (float) Setting::get($teamId, 'task_reward', 2),
            'minimum_withdrawal' => (float) Setting::get($teamId, 'minimum_withdrawal', 100),
            'maintenance_mode' => (bool) Setting::get($teamId, 'maintenance_mode', false),
            'announcement' => Setting::get($teamId, 'announcement', ''),
        ];

        return view('bunnyband::admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $teamId = auth()->user()->current_team_id;

        $validated = $request->validate([
            'welcome_bonus' => 'nullable|numeric|min:0',
            'referral_reward' => 'nullable|numeric|min:0',
            'task_reward' => 'nullable|numeric|min:0',
            'minimum_withdrawal' => 'nullable|numeric|min:0',
            'maintenance_mode' => 'nullable|boolean',
            'announcement' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                Setting::set($teamId, $key, $value);
            }
        }

        return back()->with('success', __('Settings updated.'));
    }
}
