<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialPlatform\Models\Setting;
use Modules\FinancialPlatform\Services\DeepKpiSnapshot;

class DeepKpiController extends Controller
{
    public function index(DeepKpiSnapshot $snapshot)
    {
        return view('financialplatform::kpis.deep-kpis', [
            'deepKpis' => $snapshot->forTeam(auth()->user()?->currentTeam),
            'settings' => $this->settings(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'target_sales' => 'required|numeric|min:0',
            'seostory_revenue' => 'nullable|numeric|min:0',
            'actual_manual' => 'nullable|numeric|min:0',
            'actual_source' => 'required|in:analysis,invoicemaker,seostory,manual',
            'metric_impressions_current' => 'nullable|numeric|min:0',
            'metric_impressions_previous' => 'nullable|numeric|min:0',
            'metric_clicks_current' => 'nullable|numeric|min:0',
            'metric_clicks_previous' => 'nullable|numeric|min:0',
            'metric_ctr_current' => 'nullable|numeric|min:0',
            'metric_ctr_previous' => 'nullable|numeric|min:0',
            'metric_average_position_current' => 'nullable|numeric|min:0',
            'metric_average_position_previous' => 'nullable|numeric|min:0',
            'metric_page_value_current' => 'nullable|numeric|min:0',
            'metric_page_value_previous' => 'nullable|numeric|min:0',
        ]);

        Setting::set('revenue_development_target_sales', $request->target_sales);
        Setting::set('revenue_development_actual_source', $request->actual_source);

        $this->storeIfFilled('revenue_development_seostory_revenue', $request->seostory_revenue);
        $this->storeIfFilled('revenue_development_actual_manual', $request->actual_manual);

        foreach (['impressions', 'clicks', 'ctr', 'average_position', 'page_value'] as $metric) {
            $this->storeIfFilled("deep_kpi_{$metric}_current", $request->input("metric_{$metric}_current"));
            $this->storeIfFilled("deep_kpi_{$metric}_previous", $request->input("metric_{$metric}_previous"));
        }

        return back()->with('success', 'Deep KPIs gespeichert.');
    }

    private function storeIfFilled(string $key, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        Setting::set($key, $value, is_numeric($value) ? 'decimal' : 'string');
    }

    private function settings(): array
    {
        return [
            'target_sales' => Setting::get('revenue_development_target_sales', 0),
            'actual_source' => Setting::get('revenue_development_actual_source', 'invoicemaker'),
            'actual_manual' => Setting::get('revenue_development_actual_manual', null),
            'seostory_revenue' => Setting::get('revenue_development_seostory_revenue', null),
            'metrics' => [
                'impressions_current' => Setting::get('deep_kpi_impressions_current', null),
                'impressions_previous' => Setting::get('deep_kpi_impressions_previous', null),
                'clicks_current' => Setting::get('deep_kpi_clicks_current', null),
                'clicks_previous' => Setting::get('deep_kpi_clicks_previous', null),
                'ctr_current' => Setting::get('deep_kpi_ctr_current', null),
                'ctr_previous' => Setting::get('deep_kpi_ctr_previous', null),
                'average_position_current' => Setting::get('deep_kpi_average_position_current', null),
                'average_position_previous' => Setting::get('deep_kpi_average_position_previous', null),
                'page_value_current' => Setting::get('deep_kpi_page_value_current', null),
                'page_value_previous' => Setting::get('deep_kpi_page_value_previous', null),
            ],
        ];
    }
}
