<?php

namespace Modules\BunnyBand\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\Notification;
use Modules\BunnyBand\Models\Transaction;

class WithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::where('type', 'withdrawal')
            ->with('profile.user')
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        return view('bunnyband::admin.withdrawals.index', compact('withdrawals'));
    }

    public function approve(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
            return back()->withErrors(['transaction' => __('Invalid withdrawal request.')]);
        }

        $transaction->update([
            'status' => 'approved',
            'processed_by' => auth()->id(),
            'admin_note' => $request->note,
            'processed_at' => now(),
        ]);

        Notification::create([
            'bunnyband_profile_id' => $transaction->bunnyband_profile_id,
            'type' => 'withdrawal',
            'title' => __('Withdrawal Approved'),
            'message' => __('Your withdrawal of :amount has been approved.', ['amount' => $transaction->amount]),
        ]);

        return back()->with('success', __('Withdrawal approved.'));
    }

    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
            return back()->withErrors(['transaction' => __('Invalid withdrawal request.')]);
        }

        $request->validate(['reason' => 'required|string']);

        $transaction->profile->increment('balance', $transaction->amount);

        $transaction->update([
            'status' => 'rejected',
            'processed_by' => auth()->id(),
            'admin_note' => $request->reason,
            'processed_at' => now(),
        ]);

        Notification::create([
            'bunnyband_profile_id' => $transaction->bunnyband_profile_id,
            'type' => 'withdrawal',
            'title' => __('Withdrawal Rejected'),
            'message' => __('Your withdrawal of :amount was rejected. Reason: :reason', ['amount' => $transaction->amount, 'reason' => $request->reason]),
        ]);

        return back()->with('success', __('Withdrawal rejected and refunded.'));
    }

    public function complete(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'approved') {
            return back()->withErrors(['transaction' => __('Can only complete approved withdrawals.')]);
        }

        $transaction->update([
            'status' => 'completed',
            'payment_proof' => $request->payment_proof,
            'processed_at' => now(),
        ]);

        Notification::create([
            'bunnyband_profile_id' => $transaction->bunnyband_profile_id,
            'type' => 'withdrawal',
            'title' => __('Payment Sent'),
            'message' => __('Your withdrawal of :amount has been sent.', ['amount' => $transaction->amount]),
        ]);

        return back()->with('success', __('Withdrawal marked completed.'));
    }
}
