<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\KpiDefinition;
use Modules\SmartKpi\Models\KpiRelationship;

class KpiRelationshipController extends Controller
{
    public function index(): View
    {
        $relationships = KpiRelationship::with('causeKpi', 'effectKpi')->latest()->paginate(15);

        return view('smartkpi::relationships.index', compact('relationships'));
    }

    public function create(): View
    {
        return view('smartkpi::relationships.form', [
            'relationship' => new KpiRelationship,
            'kpis' => KpiDefinition::where('is_template', false)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cause_kpi_id' => 'required|exists:smartkpi_kpi_definitions,id',
            'effect_kpi_id' => 'required|exists:smartkpi_kpi_definitions,id|different:cause_kpi_id',
            'lag_periods' => 'nullable|integer|min:0',
            'correlation' => 'nullable|numeric|between:-1,1',
            'is_active' => 'nullable|boolean',
        ]);

        KpiRelationship::create($validated + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('smartkpi.relationships.index')->with('success', __('Relationship created.'));
    }

    public function edit(KpiRelationship $relationship): View
    {
        return view('smartkpi::relationships.form', [
            'relationship' => $relationship,
            'kpis' => KpiDefinition::where('is_template', false)->get(),
        ]);
    }

    public function update(Request $request, KpiRelationship $relationship): RedirectResponse
    {
        $validated = $request->validate([
            'cause_kpi_id' => 'required|exists:smartkpi_kpi_definitions,id',
            'effect_kpi_id' => 'required|exists:smartkpi_kpi_definitions,id|different:cause_kpi_id',
            'lag_periods' => 'nullable|integer|min:0',
            'correlation' => 'nullable|numeric|between:-1,1',
            'is_active' => 'nullable|boolean',
        ]);

        $relationship->update($validated + ['is_active' => $request->boolean('is_active', $relationship->is_active ?? true)]);

        return redirect()->route('smartkpi.relationships.index')->with('success', __('Relationship updated.'));
    }

    public function destroy(KpiRelationship $relationship): RedirectResponse
    {
        $relationship->delete();

        return redirect()->route('smartkpi.relationships.index')->with('success', __('Relationship deleted.'));
    }
}
