<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialPlatform\Models\Setting;
use Modules\FinancialPlatform\Services\RevenueDevelopmentSnapshot;

class RevenueDevelopmentController extends Controller
{
    public function edit(RevenueDevelopmentSnapshot $snapshot)
    {
        return view('financialplatform::kpis.revenue-development', [
            'revenueDevelopment' => $snapshot->forTeam(auth()->user()?->currentTeam),
            'settings' => $this->settings(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'target_sales' => 'required|numeric|min:0',
            'actual_source' => 'required|in:analysis,invoicemaker,seostory,manual',
            'actual_manual' => 'nullable|numeric|min:0',
            'seostory_revenue' => 'nullable|numeric|min:0',
        ]);

        Setting::set('revenue_development_target_sales', $request->target_sales);
        Setting::set('revenue_development_actual_source', $request->actual_source);

        if ($request->filled('seostory_revenue')) {
            Setting::set('revenue_development_seostory_revenue', $request->seostory_revenue);
        }

        if ($request->filled('actual_manual')) {
            Setting::set('revenue_development_actual_manual', $request->actual_manual);
        }

        return back()->with('success', 'Revenue Development KPI gespeichert.');
    }

    private function settings(): array
    {
        return [
            'target_sales' => Setting::get('revenue_development_target_sales', 0),
            'actual_source' => Setting::get('revenue_development_actual_source', 'invoicemaker'),
            'actual_manual' => Setting::get('revenue_development_actual_manual', null),
            'seostory_revenue' => Setting::get('revenue_development_seostory_revenue', null),
        ];
    }
}
