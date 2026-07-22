<?php

namespace Modules\BunnyBand\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\BunnyBand\Models\BunnyBandProfile;
use Modules\BunnyBand\Models\Referral;
use Modules\BunnyBand\Models\Transaction;
use Modules\BunnyBand\Models\UserTask;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => BunnyBandProfile::count(),
            'blocked_users' => BunnyBandProfile::where('is_blocked', true)->count(),
            'total_paid_out' => Transaction::where('type', 'withdrawal')->where('status', 'completed')->sum('amount'),
            'pending_withdrawals' => Transaction::where('type', 'withdrawal')->where('status', 'pending')->count(),
            'pending_withdrawal_amount' => Transaction::where('type', 'withdrawal')->where('status', 'pending')->sum('amount'),
            'total_task_completions' => UserTask::where('status', 'verified')->count(),
            'pending_task_verifications' => UserTask::where('status', 'completed')->count(),
            'total_referrals' => Referral::count(),
            'today_registrations' => BunnyBandProfile::whereDate('created_at', today())->count(),
            'total_balance' => BunnyBandProfile::sum('balance'),
        ];

        $recentTransactions = Transaction::with('profile.user')->orderByDesc('created_at')->limit(20)->get();
        $recentUsers = BunnyBandProfile::with('user')->orderByDesc('created_at')->limit(10)->get();

        return view('bunnyband::admin.dashboard.index', compact('stats', 'recentTransactions', 'recentUsers'));
    }
}
