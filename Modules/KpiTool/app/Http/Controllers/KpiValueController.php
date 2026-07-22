<?php

namespace Modules\KpiTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\KpiTool\Models\KpiDefinition;
use Modules\KpiTool\Models\KpiValue;

class KpiValueController extends Controller
{
    public function index(KpiDefinition $kpiDefinition): View
    {
        $values = $kpiDefinition->values()->latest('recorded_at')->paginate(25);

        return view('kpitool::values.index', compact('kpiDefinition', 'values'));
    }

    public function store(Request $request, KpiDefinition $kpiDefinition): RedirectResponse
    {
        $validated = $request->validate([
            'value' => 'required|numeric',
            'recorded_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $kpiDefinition->values()->create($validated + ['status' => $kpiDefinition->statusFor((float) $validated['value'])]);

        return redirect()->route('kpitool.definitions.show', $kpiDefinition)->with('success', __('Value recorded.'));
    }

    public function edit(KpiValue $kpiValue): View
    {
        return view('kpitool::values.form', ['value' => $kpiValue, 'definition' => $kpiValue->kpiDefinition]);
    }

    public function update(Request $request, KpiValue $kpiValue): RedirectResponse
    {
        $validated = $request->validate([
            'value' => 'required|numeric',
            'recorded_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $kpiValue->update($validated);

        return redirect()->route('kpitool.definitions.show', $kpiValue->kpiDefinition)->with('success', __('Value updated.'));
    }

    public function destroy(KpiValue $kpiValue): RedirectResponse
    {
        $definition = $kpiValue->kpiDefinition;
        $kpiValue->delete();

        return redirect()->route('kpitool.definitions.show', $definition)->with('success', __('Value deleted.'));
    }
}
