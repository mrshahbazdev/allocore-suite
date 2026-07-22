<?php

namespace Modules\BunnyBand\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\BunnyBand\Models\BunnyBandProfile;
use Modules\BunnyBand\Models\Setting;
use Modules\BunnyBand\Models\Task;
use Modules\BunnyBand\Models\Transaction;
use Modules\BunnyBand\Models\UserTask;

class DashboardController extends Controller
{
    public function index(): View
    {
        $profile = BunnyBandProfile::forCurrentUser();
        $teamId = auth()->user()->current_team_id;

        if (! $profile->wasRecentlyCreated) {
            $welcomeBonus = (float) Setting::get($teamId, 'welcome_bonus', 10);
            if ($welcomeBonus > 0 && ! Transaction::where('bunnyband_profile_id', $profile->id)->where('type', 'welcome_bonus')->exists()) {
                $profile->increment('balance', $welcomeBonus);
                Transaction::create([
                    'bunnyband_profile_id' => $profile->id,
                    'type' => 'welcome_bonus',
                    'amount' => $welcomeBonus,
                    'status' => 'completed',
                    'description' => __('Welcome bonus'),
                ]);
            }
        }

        $tasks = Task::where('is_active', true)->orderByDesc('created_at')->limit(5)->get();
        $completedToday = UserTask::where('bunnyband_profile_id', $profile->id)
            ->where('status', 'verified')
            ->whereDate('created_at', today())
            ->count();

        return view('bunnyband::dashboard.index', compact('profile', 'tasks', 'completedToday'));
    }
}
