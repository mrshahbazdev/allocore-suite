<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialPlatform\Models\KpiThreshold;

class ThresholdController extends Controller
{
    public function index()
    {
        $thresholds = KpiThreshold::orderBy('tool')->orderBy('kpi_code')->get()->groupBy('tool');

        return view('admin.thresholds.index', compact('thresholds'));
    }

    public function update(Request $request, KpiThreshold $threshold)
    {
        $validated = $request->validate([
            'green_min' => 'nullable|numeric',
            'green_max' => 'nullable|numeric',
            'yellow_min' => 'nullable|numeric',
            'yellow_max' => 'nullable|numeric',
            'weight' => 'nullable|numeric|min:0',
            'lower_is_better' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['lower_is_better'] = $request->boolean('lower_is_better');
        $validated['is_active'] = $request->boolean('is_active');

        $threshold->update($validated);

        return back()->with('success', __('Threshold updated.'));
    }
}
