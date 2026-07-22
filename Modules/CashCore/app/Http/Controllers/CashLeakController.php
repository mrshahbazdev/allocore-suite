<?php

namespace Modules\CashCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CashCore\Models\CashLeak;
use Modules\CashCore\Services\CashCoreService;

class CashLeakController extends Controller
{
    public function __construct(private CashCoreService $service) {}

    public function index(): View
    {
        $activeLeaks = CashLeak::active()->orderByDesc('leak_score')->get();
        $resolvedLeaks = CashLeak::resolved()->latest()->get();
        $overallScore = $this->service->overallLeakScore(auth()->user()->current_team_id);
        $totalLeakAmount = $activeLeaks->sum('monthly_amount');

        return view('cashcore::leaks.index', compact('activeLeaks', 'resolvedLeaks', 'overallScore', 'totalLeakAmount'));
    }

    public function runDetection(): RedirectResponse
    {
        $leaks = $this->service->runLeakDetection(auth()->user()->current_team_id);

        return redirect()->route('cashcore.leaks.index')
            ->with('success', count($leaks) > 0 ? __('Detected :count leaks.', ['count' => count($leaks)]) : __('No leaks found.'));
    }

    public function updateStatus(Request $request, CashLeak $leak): RedirectResponse
    {
        $request->validate(['status' => 'required|in:detected,reviewed,resolved,ignored']);
        $leak->update(['status' => $request->status]);

        return redirect()->route('cashcore.leaks.index')->with('success', __('Leak updated.'));
    }
}
