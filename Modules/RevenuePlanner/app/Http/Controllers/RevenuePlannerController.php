<?php

namespace Modules\RevenuePlanner\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\RevenuePlanner\Models\RevenuePlan;
use Modules\RevenuePlanner\Models\RevenueScenario;
use Modules\RevenuePlanner\Services\InvoiceMakerBridgeService;
use Modules\RevenuePlanner\Services\RevenueCalculationService;

class RevenuePlannerController extends Controller
{
    public function __construct(
        private readonly RevenueCalculationService $calcService,
        private readonly InvoiceMakerBridgeService $bridgeService,
    ) {}

    public function index(): View
    {
        $plans = RevenuePlan::latest()->get();
        $activePlan = $plans->firstWhere('status', 'active') ?? $plans->first();

        return view('revenueplanner::dashboard', compact('plans', 'activePlan'));
    }

    public function wizard(?RevenuePlan $plan = null): View
    {
        $plan = $plan ?? RevenuePlan::latest()->first() ?? new RevenuePlan([
            'fiscal_year' => date('Y'),
            'name' => 'Umsatzplan ' . date('Y'),
            'contribution_margin_percent' => 100,
            'average_deal_size' => 2500,
            'lead_to_close_rate_percent' => 20,
            'seasonality_pattern' => 'even',
        ]);

        $teamId = auth()->user()->current_team_id;
        $invoiceData = $this->bridgeService->pullFromInvoiceMaker($teamId);

        return view('revenueplanner::wizard.index', compact('plan', 'invoiceData'));
    }

    public function saveWizard(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'industry_type' => ['nullable', 'string', 'max:64'],
            'fiscal_year' => ['required', 'integer', 'min:2020', 'max:2035'],
            'fixed_costs_annual' => ['nullable', 'numeric', 'min:0'],
            'fixed_costs_breakdown' => ['nullable', 'array'],
            'owner_compensation_annual' => ['nullable', 'numeric', 'min:0'],
            'owner_compensation_breakdown' => ['nullable', 'array'],
            'profit_target_annual' => ['nullable', 'numeric', 'min:0'],
            'contribution_margin_percent' => ['required', 'numeric', 'min:1', 'max:100'],
            'actual_revenue_annual' => ['nullable', 'numeric', 'min:0'],
            'existing_customer_levers' => ['nullable', 'array'],
            'average_deal_size' => ['required', 'numeric', 'min:1'],
            'lead_to_close_rate_percent' => ['required', 'numeric', 'min:1', 'max:100'],
            'seasonality_pattern' => ['required', 'string', 'in:even,summer_dip,q4_peak,b2b_service'],
            'notes' => ['nullable', 'string'],
        ]);

        $computed = $this->calcService->calculate($validated);
        $payload = array_merge($validated, $computed, [
            'team_id' => auth()->user()->current_team_id,
            'user_id' => auth()->id(),
            'status' => 'active',
        ]);

        $planId = $request->input('plan_id');
        if ($planId && ($existing = RevenuePlan::find($planId))) {
            $existing->update($payload);
            $plan = $existing;
        } else {
            $plan = RevenuePlan::create($payload);
        }

        return redirect()->route('revenueplanner.plans.show', $plan->id)
            ->with('status', __('Revenue Plan successfully calculated & saved!'));
    }

    public function show(RevenuePlan $plan): View
    {
        $plan->loadMissing('scenarios');
        return view('revenueplanner::show', compact('plan'));
    }

    public function syncInvoiceMaker(RevenuePlan $plan): RedirectResponse
    {
        $teamId = auth()->user()->current_team_id;
        $syncData = $this->bridgeService->pullFromInvoiceMaker($teamId);

        if (! $syncData['has_data']) {
            return back()->with('warning', __('No paid invoices or expense records found in InvoiceMaker yet.'));
        }

        $input = array_merge($plan->toArray(), [
            'actual_revenue_annual' => $syncData['actual_revenue_annual'] > 0 ? $syncData['actual_revenue_annual'] : $plan->actual_revenue_annual,
            'fixed_costs_annual' => $syncData['fixed_costs_annual'] > 0 ? $syncData['fixed_costs_annual'] : $plan->fixed_costs_annual,
        ]);

        $computed = $this->calcService->calculate($input);
        $plan->update($computed);

        return back()->with('status', __('Synced :count paid invoices & recorded expenses from InvoiceMaker successfully.', ['count' => $syncData['invoice_count']]));
    }

    public function storeScenario(Request $request, RevenuePlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'price_increase_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'deal_size_multiplier' => ['nullable', 'numeric', 'min:0.5', 'max:5'],
            'conversion_rate_delta_percent' => ['nullable', 'numeric', 'min:-50', 'max:50'],
            'lead_volume_multiplier' => ['nullable', 'numeric', 'min:0.5', 'max:5'],
        ]);

        $priceIncrease = (float) ($validated['price_increase_percent'] ?? 0);
        $dealMult = (float) ($validated['deal_size_multiplier'] ?? 1.0);
        $convDelta = (float) ($validated['conversion_rate_delta_percent'] ?? 0);

        // Calculate simulated revenue
        $simActual = $plan->actual_revenue_annual * (1 + ($priceIncrease / 100));
        $simGap = max(0.0, round($plan->target_revenue_annual - $simActual, 2));

        $simDealSize = $plan->average_deal_size * $dealMult;
        $simCloseRate = max(1.0, min(100.0, $plan->lead_to_close_rate_percent + $convDelta));
        
        $simDealsNeeded = (int) ceil($simGap / max(1.0, $simDealSize));
        $simLeadsNeeded = (int) ceil($simDealsNeeded / ($simCloseRate / 100));

        RevenueScenario::create([
            'team_id' => $plan->team_id,
            'user_id' => auth()->id(),
            'plan_id' => $plan->id,
            'title' => $validated['title'],
            'price_increase_percent' => $priceIncrease,
            'deal_size_multiplier' => $dealMult,
            'conversion_rate_delta_percent' => $convDelta,
            'lead_volume_multiplier' => (float) ($validated['lead_volume_multiplier'] ?? 1.0),
            'simulated_annual_revenue' => $simActual,
            'simulated_revenue_gap' => $simGap,
            'simulated_required_leads' => $simLeadsNeeded,
        ]);

        return back()->with('status', __('Scenario successfully simulated!'));
    }

    public function destroy(RevenuePlan $plan): RedirectResponse
    {
        $plan->delete();
        return redirect()->route('revenueplanner.dashboard')->with('status', __('Plan deleted.'));
    }
}
