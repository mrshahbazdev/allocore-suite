<?php

namespace Modules\BunnyBand\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\BunnyBandProfile;
use Modules\BunnyBand\Models\Referral;
use Modules\BunnyBand\Models\Setting;
use Modules\BunnyBand\Models\Transaction;

class ReferralController extends Controller
{
    public function index(): View
    {
        $profile = BunnyBandProfile::forCurrentUser();
        $referrals = Referral::where('referrer_id', $profile->id)
            ->with('referred.user')
            ->orderByDesc('created_at')
            ->paginate(20);

        $link = url('/r/'.$profile->referral_code);

        return view('bunnyband::referrals.index', compact('profile', 'referrals', 'link'));
    }

    public function claim(Request $request): RedirectResponse
    {
        $profile = BunnyBandProfile::forCurrentUser();

        if ($profile->referred_by) {
            return back()->withErrors(['code' => __('Referral already claimed.')]);
        }

        $validated = $request->validate(['referral_code' => 'required|string|exists:bunnyband_profiles,referral_code']);

        $referrer = BunnyBandProfile::where('referral_code', $validated['referral_code'])->first();

        if (! $referrer || $referrer->id === $profile->id || $referrer->team_id !== $profile->team_id) {
            return back()->withErrors(['code' => __('Invalid referral code.')]);
        }

        $reward = (float) Setting::get($profile->team_id, 'referral_reward', 5);

        $profile->update(['referred_by' => $referrer->id]);

        Referral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $profile->id,
            'reward_amount' => $reward,
            'is_rewarded' => true,
        ]);

        $referrer->increment('balance', $reward);
        $referrer->increment('referral_earnings', $reward);
        $referrer->increment('total_referrals');

        Transaction::create([
            'bunnyband_profile_id' => $referrer->id,
            'type' => 'referral_earning',
            'amount' => $reward,
            'status' => 'completed',
            'description' => __('Referral bonus'),
        ]);

        return back()->with('success', __('Referral claimed!'));
    }
}
