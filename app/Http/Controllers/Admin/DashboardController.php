<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'users' => User::count(),
            'admins' => User::whereHas('roles', fn ($query) => $query->where('name', 'admin'))->count(),
            'teams' => Team::count(),
            'modules' => Module::where('is_active', true)->count(),
            'plans' => Plan::count(),
            'subscriptions' => ToolSubscription::count(),
            'pending_bank' => ToolSubscription::where('payment_method', 'bank')->where('status', 'pending')->count(),
        ];

        $recentUsers = User::with('currentTeam')->latest()->limit(8)->get();
        $recentSubscriptions = ToolSubscription::with(['plan', 'billable'])->latest()->limit(8)->get();

        return view('admin.index', compact('stats', 'recentUsers', 'recentSubscriptions'));
    }
}
