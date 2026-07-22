<?php

namespace Modules\BunnyBand\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\BunnyBandProfile;
use Modules\BunnyBand\Models\DepositMethod;
use Modules\BunnyBand\Models\PaymentMethod;
use Modules\BunnyBand\Models\Setting;
use Modules\BunnyBand\Models\Transaction;
use Modules\BunnyBand\Models\WithdrawalMethod;

class WalletController extends Controller
{
    public function index(): View
    {
        $profile = BunnyBandProfile::forCurrentUser();
        $transactions = Transaction::where('bunnyband_profile_id', $profile->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $pendingWithdrawal = Transaction::where('bunnyband_profile_id', $profile->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->first();

        $pendingDeposit = Transaction::where('bunnyband_profile_id', $profile->id)
            ->where('type', 'deposit')
            ->where('status', 'pending')
            ->first();

        $withdrawalMethods = WithdrawalMethod::where('is_active', true)->get();
        $depositMethods = DepositMethod::where('is_active', true)->get();
        $paymentMethods = PaymentMethod::where('bunnyband_profile_id', $profile->id)->get();
        $minimumWithdrawal = (float) Setting::get($profile->team_id, 'minimum_withdrawal', 100);

        return view('bunnyband::wallet.index', compact('profile', 'transactions', 'pendingWithdrawal', 'pendingDeposit', 'withdrawalMethods', 'depositMethods', 'paymentMethods', 'minimumWithdrawal'));
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $profile = BunnyBandProfile::forCurrentUser();

        $pending = Transaction::where('bunnyband_profile_id', $profile->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return back()->withErrors(['withdraw' => __('You already have a pending withdrawal.')]);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'bunnyband_withdrawal_method_id' => 'required|exists:bunnyband_withdrawal_methods,id',
            'account_details' => 'required|string',
        ]);

        $method = WithdrawalMethod::where('id', $validated['bunnyband_withdrawal_method_id'])
            ->where('is_active', true)
            ->first();

        if (! $method) {
            return back()->withErrors(['method' => __('Method not available.')]);
        }

        if ($validated['amount'] > $profile->balance) {
            return back()->withErrors(['amount' => __('Insufficient balance.')]);
        }

        $minimum = (float) Setting::get($profile->team_id, 'minimum_withdrawal', 100);
        if ($validated['amount'] < $minimum) {
            return back()->withErrors(['amount' => __('Minimum withdrawal is :amount', ['amount' => $minimum])]);
        }

        $profile->decrement('balance', $validated['amount']);

        Transaction::create([
            'bunnyband_profile_id' => $profile->id,
            'type' => 'withdrawal',
            'amount' => $validated['amount'],
            'status' => 'pending',
            'payment_method' => $method->name,
            'bunnyband_withdrawal_method_id' => $method->id,
            'description' => $validated['account_details'],
        ]);

        return back()->with('success', __('Withdrawal request submitted.'));
    }

    public function deposit(Request $request): RedirectResponse
    {
        $profile = BunnyBandProfile::forCurrentUser();

        $pending = Transaction::where('bunnyband_profile_id', $profile->id)
            ->where('type', 'deposit')
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return back()->withErrors(['deposit' => __('You already have a pending deposit request.')]);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'bunnyband_deposit_method_id' => 'required|exists:bunnyband_deposit_methods,id',
            'screenshot' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'account_details' => 'required|string',
        ]);

        $method = DepositMethod::where('id', $validated['bunnyband_deposit_method_id'])
            ->where('is_active', true)
            ->first();

        if (! $method) {
            return back()->withErrors(['method' => __('Method not available.')]);
        }

        $path = $request->file('screenshot')->store('bunnyband/deposits', 'public');

        Transaction::create([
            'bunnyband_profile_id' => $profile->id,
            'type' => 'deposit',
            'amount' => $validated['amount'],
            'status' => 'pending',
            'payment_method' => $method->name,
            'screenshot' => $path,
            'description' => $validated['account_details'],
        ]);

        return back()->with('success', __('Deposit request submitted.'));
    }

    public function savePaymentMethod(Request $request): RedirectResponse
    {
        $profile = BunnyBandProfile::forCurrentUser();

        $validated = $request->validate([
            'type' => 'required|in:automatic,manual',
            'method' => 'required|string',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
        ]);

        PaymentMethod::where('bunnyband_profile_id', $profile->id)->update(['is_default' => false]);

        PaymentMethod::updateOrCreate(
            ['bunnyband_profile_id' => $profile->id, 'method' => $validated['method']],
            $validated + ['is_default' => true]
        );

        return back()->with('success', __('Payment method saved.'));
    }
}
