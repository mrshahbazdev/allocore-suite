<?php

namespace Modules\BunnyBand\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\Notification;
use Modules\BunnyBand\Models\Transaction;

class DepositController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::where('type', 'deposit')
            ->with('profile.user')
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $deposits = $query->paginate(20)->withQueryString();

        return view('bunnyband::admin.deposits.index', compact('deposits'));
    }

    public function approve(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'deposit' || $transaction->status !== 'pending') {
            return back()->withErrors(['transaction' => __('Invalid deposit request.')]);
        }

        $transaction->profile->increment('balance', $transaction->amount);

        $transaction->update([
            'status' => 'approved',
            'processed_by' => auth()->id(),
            'admin_note' => $request->note,
            'processed_at' => now(),
        ]);

        Notification::create([
            'bunnyband_profile_id' => $transaction->bunnyband_profile_id,
            'type' => 'deposit',
            'title' => __('Deposit Approved'),
            'message' => __('Your deposit of :amount has been approved.', ['amount' => $transaction->amount]),
        ]);

        return back()->with('success', __('Deposit approved and balance credited.'));
    }

    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'deposit' || $transaction->status !== 'pending') {
            return back()->withErrors(['transaction' => __('Invalid deposit request.')]);
        }

        $request->validate(['reason' => 'required|string']);

        $transaction->update([
            'status' => 'rejected',
            'processed_by' => auth()->id(),
            'admin_note' => $request->reason,
            'processed_at' => now(),
        ]);

        return back()->with('success', __('Deposit rejected.'));
    }
}
