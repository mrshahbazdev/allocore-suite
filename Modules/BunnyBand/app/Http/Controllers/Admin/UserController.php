<?php

namespace Modules\BunnyBand\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\BunnyBandProfile;
use Modules\BunnyBand\Models\Transaction;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = BunnyBandProfile::with('user')->orderByDesc('created_at');

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->status === 'blocked') {
            $query->where('is_blocked', true);
        } elseif ($request->status === 'active') {
            $query->where('is_blocked', false);
        }

        $profiles = $query->paginate(20)->withQueryString();

        return view('bunnyband::admin.users.index', compact('profiles'));
    }

    public function show(BunnyBandProfile $profile): View
    {
        $profile->load(['referrals.referred.user', 'transactions', 'tasks.task', 'paymentMethods']);

        return view('bunnyband::admin.users.show', compact('profile'));
    }

    public function block(BunnyBandProfile $profile): RedirectResponse
    {
        $profile->update(['is_blocked' => ! $profile->is_blocked]);

        return back()->with('success', $profile->is_blocked ? __('User blocked.') : __('User unblocked.'));
    }

    public function adjustBalance(Request $request, BunnyBandProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:add,subtract',
            'reason' => 'required|string',
        ]);

        if ($validated['type'] === 'add') {
            $profile->increment('balance', $validated['amount']);
            $type = 'deposit';
        } else {
            $profile->decrement('balance', $validated['amount']);
            $type = 'withdrawal';
        }

        Transaction::create([
            'bunnyband_profile_id' => $profile->id,
            'type' => $type,
            'amount' => $validated['amount'],
            'status' => 'completed',
            'description' => $validated['reason'],
            'processed_by' => auth()->id(),
        ]);

        return back()->with('success', __('Balance adjusted.'));
    }
}
