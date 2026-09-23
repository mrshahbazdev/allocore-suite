<?php

namespace Modules\RevenuePlanner\Services;

class RevenueCalculationService
{
    /**
     * Compute the complete revenue plan numbers from raw input data.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        // 1. Fixed Costs calculation
        $breakdownCosts = $input['fixed_costs_breakdown'] ?? [];
        $totalFixedAnnual = 0.0;
        foreach (['personnel', 'rent', 'insurance', 'vehicles', 'software', 'telecom', 'marketing', 'financing', 'other'] as $k) {
            $val = (float) ($breakdownCosts[$k] ?? 0);
            $totalFixedAnnual += $val;
        }

        if (isset($input['fixed_costs_annual']) && (float) $input['fixed_costs_annual'] > 0) {
            $totalFixedAnnual = (float) $input['fixed_costs_annual'];
        }
        $totalFixedMonthly = round($totalFixedAnnual / 12, 2);

        // 2. Owner Compensation
        $breakdownComp = $input['owner_compensation_breakdown'] ?? [];
        $netLiving = (float) ($breakdownComp['net_living'] ?? 0);
        $pension = (float) ($breakdownComp['pension_health'] ?? 0);
        $taxBuffer = (float) ($breakdownComp['tax_buffer'] ?? 0);
        $totalCompAnnual = $netLiving + $pension + $taxBuffer;

        if (isset($input['owner_compensation_annual']) && (float) $input['owner_compensation_annual'] > 0) {
            $totalCompAnnual = (float) $input['owner_compensation_annual'];
        }
        $totalCompMonthly = round($totalCompAnnual / 12, 2);

        // 3. Profit Target & Margin
        $profitTarget = (float) ($input['profit_target_annual'] ?? 0);
        $marginPercent = (float) ($input['contribution_margin_percent'] ?? 100);
        if ($marginPercent <= 0 || $marginPercent > 100) {
            $marginPercent = 100;
        }
        $marginFactor = $marginPercent / 100;

        // Required Gross Contribution & Target Revenue
        $requiredGrossContribution = $totalFixedAnnual + $totalCompAnnual + $profitTarget;
        $targetRevenueAnnual = round($requiredGrossContribution / $marginFactor, 2);
        $targetRevenueMonthly = round($targetRevenueAnnual / 12, 2);

        // 4. Actual Revenue & Gap
        $actualRevenueAnnual = (float) ($input['actual_revenue_annual'] ?? 0);
        if ($actualRevenueAnnual <= 0 && isset($input['actual_revenue_monthly'])) {
            $actualRevenueAnnual = (float) $input['actual_revenue_monthly'] * 12;
        }
        $actualRevenueMonthly = round($actualRevenueAnnual / 12, 2);

        $revenueGapAnnual = max(0.0, round($targetRevenueAnnual - $actualRevenueAnnual, 2));
        $revenueGapMonthly = round($revenueGapAnnual / 12, 2);
        $coverageRatio = $targetRevenueAnnual > 0 ? round(($actualRevenueAnnual / $targetRevenueAnnual) * 100, 1) : 100.0;

        // 5. Existing Customer Levers
        $levers = $input['existing_customer_levers'] ?? [];
        $priceIncreasePercent = (float) ($levers['price_increase_percent'] ?? 0);
        $repeatPurchaseIncreasePercent = (float) ($levers['repeat_purchase_increase_percent'] ?? 0);
        $crossSellingRevenue = (float) ($levers['cross_selling_revenue'] ?? 0);
        $churnReductionRevenue = (float) ($levers['churn_reduction_revenue'] ?? 0);

        $priceGain = $actualRevenueAnnual * ($priceIncreasePercent / 100);
        $repeatGain = $actualRevenueAnnual * ($repeatPurchaseIncreasePercent / 100);
        $existingCustomerPotential = round($priceGain + $repeatGain + $crossSellingRevenue + $churnReductionRevenue, 2);

        // Remaining Gap
        $remainingGapAnnual = max(0.0, round($revenueGapAnnual - $existingCustomerPotential, 2));
        $remainingGapMonthly = round($remainingGapAnnual / 12, 2);

        // 6. Reverse Sales Funnel for New Customers
        $avgDealSize = (float) ($input['average_deal_size'] ?? 2500);
        if ($avgDealSize <= 0) {
            $avgDealSize = 2500;
        }

        $closeRatePercent = (float) ($input['lead_to_close_rate_percent'] ?? 20);
        if ($closeRatePercent <= 0 || $closeRatePercent > 100) {
            $closeRatePercent = 20;
        }
        $closeRateFactor = $closeRatePercent / 100;

        $requiredWonDealsAnnual = (int) ceil($remainingGapAnnual / $avgDealSize);
        $requiredWonDealsMonthly = (int) ceil($requiredWonDealsAnnual / 12);

        $requiredLeadsAnnual = (int) ceil($requiredWonDealsAnnual / $closeRateFactor);
        $requiredLeadsMonthly = (int) ceil($requiredLeadsAnnual / 12);

        // 7. Seasonality & 12-Month Distribution
        $seasonality = $input['seasonality_pattern'] ?? 'even';
        $monthlyDistribution = $this->calculateMonthlyDistribution($targetRevenueAnnual, $actualRevenueAnnual, $seasonality);

        // 8. 90-Day Action Roadmap
        $actionRoadmap = $input['action_roadmap'] ?? $this->defaultActionRoadmap($revenueGapAnnual, $existingCustomerPotential, $requiredLeadsMonthly);

        return [
            'fixed_costs_annual' => $totalFixedAnnual,
            'fixed_costs_monthly' => $totalFixedMonthly,
            'fixed_costs_breakdown' => $breakdownCosts,
            'owner_compensation_annual' => $totalCompAnnual,
            'owner_compensation_monthly' => $totalCompMonthly,
            'owner_compensation_breakdown' => $breakdownComp,
            'profit_target_annual' => $profitTarget,
            'contribution_margin_percent' => $marginPercent,
            'target_revenue_annual' => $targetRevenueAnnual,
            'target_revenue_monthly' => $targetRevenueMonthly,
            'actual_revenue_annual' => $actualRevenueAnnual,
            'actual_revenue_monthly' => $actualRevenueMonthly,
            'revenue_gap_annual' => $revenueGapAnnual,
            'revenue_gap_monthly' => $revenueGapMonthly,
            'coverage_ratio_percent' => $coverageRatio,
            'existing_customer_potential_annual' => $existingCustomerPotential,
            'existing_customer_levers' => array_merge($levers, [
                'price_gain' => $priceGain,
                'repeat_gain' => $repeatGain,
                'cross_selling_revenue' => $crossSellingRevenue,
                'churn_reduction_revenue' => $churnReductionRevenue,
            ]),
            'remaining_gap_annual' => $remainingGapAnnual,
            'remaining_gap_monthly' => $remainingGapMonthly,
            'average_deal_size' => $avgDealSize,
            'lead_to_close_rate_percent' => $closeRatePercent,
            'required_won_deals_annual' => $requiredWonDealsAnnual,
            'required_won_deals_monthly' => $requiredWonDealsMonthly,
            'required_leads_annual' => $requiredLeadsAnnual,
            'required_leads_monthly' => $requiredLeadsMonthly,
            'seasonality_pattern' => $seasonality,
            'monthly_distribution' => $monthlyDistribution,
            'action_roadmap' => $actionRoadmap,
        ];
    }

    /**
     * Compute 12-month revenue targets based on seasonality patterns.
     *
     * @return array<int, array{month: string, weight_percent: float, target: float, actual_estimate: float}>
     */
    public function calculateMonthlyDistribution(float $targetAnnual, float $actualAnnual, string $pattern): array
    {
        $months = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
        
        $weights = match ($pattern) {
            'summer_dip' => [0.08, 0.08, 0.09, 0.09, 0.09, 0.08, 0.06, 0.05, 0.09, 0.10, 0.10, 0.09], // August/July dip
            'q4_peak' => [0.07, 0.07, 0.08, 0.08, 0.08, 0.08, 0.07, 0.07, 0.09, 0.11, 0.12, 0.08], // Q4 strong
            'b2b_service' => [0.07, 0.08, 0.09, 0.09, 0.09, 0.09, 0.07, 0.06, 0.09, 0.10, 0.10, 0.07], // Standard B2B
            default => array_fill(0, 12, 1 / 12), // Even distribution
        };

        $distribution = [];
        foreach ($months as $i => $mName) {
            $w = $weights[$i] ?? (1 / 12);
            $distribution[] = [
                'month' => $mName,
                'weight_percent' => round($w * 100, 1),
                'target' => round($targetAnnual * $w, 2),
                'actual_estimate' => round($actualAnnual * $w, 2),
            ];
        }

        return $distribution;
    }

    /**
     * Build default 90-day action items based on gaps.
     *
     * @return array<int, array{priority: string, category: string, title: string, description: string, status: string}>
     */
    public function defaultActionRoadmap(float $gap, float $existingPotential, int $monthlyLeads): array
    {
        $items = [];

        if ($existingPotential > 0) {
            $items[] = [
                'priority' => 'high',
                'category' => 'Bestandskunden',
                'title' => 'Preisanpassung für Bestandskunden vorbereiten',
                'description' => 'Bestehende Verträge und Stundensätze analysieren und selektiv um 5–10% anpassen.',
                'status' => 'pending',
            ];
            $items[] = [
                'priority' => 'high',
                'category' => 'Bestandskunden',
                'title' => 'Wiederkauf- & Service-Kampagne starten',
                'description' => 'Inaktive Kunden der letzten 12 Monate mit einem konkreten Mehrwert-Angebot kontaktieren.',
                'status' => 'pending',
            ];
        }

        if ($gap > 0) {
            $items[] = [
                'priority' => 'critical',
                'category' => 'Vertrieb',
                'title' => "Monatlich {$monthlyLeads} qualifizierte Leads generieren",
                'description' => 'Vertriebsaktivitäten, Outbound und Empfehlungsmarketing auf das kalkulierte Lead-Ziel ausrichten.',
                'status' => 'in_progress',
            ];
            $items[] = [
                'priority' => 'medium',
                'category' => 'Controlling',
                'title' => 'Monatlichen Soll-Ist-Vergleich etablieren',
                'description' => 'Fixkosten und Umsatzeingänge monatlich im Umsatzplaner mit den Soll-Zielen abgleichen.',
                'status' => 'pending',
            ];
        } else {
            $items[] = [
                'priority' => 'medium',
                'category' => 'Rücklagen',
                'title' => 'Gewinnrücklagen sichern & Liquiditätspuffer aufbauen',
                'description' => 'Monatlich mindestens 10–15% des Rohertrags auf ein separates Rücklagenkonto überweisen.',
                'status' => 'pending',
            ];
        }

        return $items;
    }
}
