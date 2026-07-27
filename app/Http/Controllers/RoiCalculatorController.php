<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class RoiCalculatorController extends Controller
{
    public function index(Request $request): View
    {
        $result = null;

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'employees' => 'required|integer|min:1|max:10000',
                'hourly_rate' => 'required|numeric|min:0',
                'hours_saved_per_week' => 'required|numeric|min:0|max:100',
                'annual_revenue' => 'required|numeric|min:0',
                'revenue_growth_percent' => 'required|numeric|min:0|max:100',
            ]);

            $result = $this->calculate($validated);
        }

        return view('roi-calculator', [
            'result' => $result,
            'input' => $request->all(),
        ]);
    }

    private function calculate(array $data): array
    {
        $employees = (float) $data['employees'];
        $hourlyRate = (float) $data['hourly_rate'];
        $hoursSavedPerWeek = (float) $data['hours_saved_per_week'];
        $annualRevenue = (float) $data['annual_revenue'];
        $revenueGrowthPercent = (float) $data['revenue_growth_percent'];

        $weeksPerYear = 48;
        $timeSavings = $employees * $hoursSavedPerWeek * $weeksPerYear * $hourlyRate;
        $revenuePotential = $annualRevenue * ($revenueGrowthPercent / 100);
        $profitLift = $revenuePotential * 0.15;

        return [
            'time_savings' => round($timeSavings, 2),
            'revenue_potential' => round($revenuePotential, 2),
            'profit_lift' => round($profitLift, 2),
            'total_benefit' => round($timeSavings + $revenuePotential + $profitLift, 2),
        ];
    }
}
