<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\KpiDefinition;
use Modules\SmartKpi\Models\KpiValue;
use Modules\SmartKpi\Services\ProblemDetectionService;

class KpiValueController extends Controller
{
    public function __construct(protected ProblemDetectionService $detector) {}

    public function create(KpiDefinition $kpiDefinition): View
    {
        return view('smartkpi::kpi-values.form', ['value' => new KpiValue, 'kpi' => $kpiDefinition]);
    }

    public function store(Request $request, KpiDefinition $kpiDefinition): RedirectResponse
    {
        $validated = $request->validate([
            'value' => 'required|numeric',
            'recorded_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;
        $validated['kpi_definition_id'] = $kpiDefinition->id;
        $validated['recorded_by'] = auth()->id();
        $validated['status'] = $kpiDefinition->statusForValue((float) $validated['value']);

        $value = KpiValue::create($validated);

        $this->detector->detect($value);
        $this->detector->evaluateAlertRules($kpiDefinition, $value);

        return redirect()->route('smartkpi.kpi-definitions.show', $kpiDefinition)->with('success', __('Value recorded.'));
    }

    public function destroy(KpiValue $kpiValue): RedirectResponse
    {
        $kpi = $kpiValue->kpiDefinition;
        $kpiValue->delete();

        return redirect()->route('smartkpi.kpi-definitions.show', $kpi)->with('success', __('Value deleted.'));
    }
}
