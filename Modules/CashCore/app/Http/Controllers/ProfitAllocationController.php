<?php

namespace Modules\CashCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CashCore\Models\CashTransaction;
use Modules\CashCore\Models\ProfitAllocation;
use Modules\CashCore\Services\CashCoreService;

class ProfitAllocationController extends Controller
{
    public function __construct(private CashCoreService $service) {}

    public function index(Request $request): View
    {
        $period = $request->get('period', now()->format('Y-m'));

        $this->service->calculateProfitAllocations(auth()->user()->current_team_id, $period);

        $allocations = ProfitAllocation::where('period', $period)->get();
        $totalRevenue = CashTransaction::income()->forPeriod($period)->sum('amount');

        return view('cashcore::allocation.index', compact('allocations', 'totalRevenue', 'period'));
    }

    public function update(Request $request): RedirectResponse
    {
        $period = $request->input('period', now()->format('Y-m'));

        $request->validate([
            'allocations' => 'required|array',
            'allocations.*.bucket' => 'required|string',
            'allocations.*.percentage' => 'required|numeric|min:0|max:100',
        ]);

        $total = collect($request->allocations)->sum('percentage');
        if (abs($total - 100) > 0.01) {
            return back()->withErrors(['allocations' => __('Allocations must total 100%.')]);
        }

        foreach ($request->allocations as $data) {
            ProfitAllocation::updateOrCreate(
                ['bucket' => $data['bucket'], 'period' => $period],
                ['percentage' => $data['percentage']]
            );
        }

        $this->service->calculateProfitAllocations(auth()->user()->current_team_id, $period);

        return redirect()->route('cashcore.allocation.index', ['period' => $period])->with('success', __('Allocations saved.'));
    }
}
