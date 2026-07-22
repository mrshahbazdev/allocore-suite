<?php

namespace Modules\BunnyBand\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\BunnyBand\Models\BunnyBandProfile;
use Modules\BunnyBand\Models\Level;
use Modules\BunnyBand\Models\Transaction;

class LevelController extends Controller
{
    public function index(): View
    {
        $profile = BunnyBandProfile::forCurrentUser();
        $levels = Level::where('is_active', true)->orderBy('sort_order')->get();

        return view('bunnyband::levels.index', compact('profile', 'levels'));
    }

    public function upgrade(Level $level): RedirectResponse
    {
        $profile = BunnyBandProfile::forCurrentUser();

        if ($profile->level_id === $level->id) {
            return back()->withErrors(['level' => __('You already have this level.')]);
        }

        if ($level->type === 'free') {
            $profile->update(['level_id' => $level->id, 'level_upgraded_at' => now()]);

            return back()->with('success', __('Level upgraded!'));
        }

        if ($profile->balance < $level->price) {
            return back()->withErrors(['level' => __('Insufficient balance.')]);
        }

        $profile->decrement('balance', $level->price);
        $profile->update(['level_id' => $level->id, 'level_upgraded_at' => now()]);

        Transaction::create([
            'bunnyband_profile_id' => $profile->id,
            'type' => 'withdrawal',
            'amount' => $level->price,
            'status' => 'completed',
            'description' => __('Level upgrade to :name', ['name' => $level->name]),
        ]);

        return back()->with('success', __('Level upgraded!'));
    }
}
