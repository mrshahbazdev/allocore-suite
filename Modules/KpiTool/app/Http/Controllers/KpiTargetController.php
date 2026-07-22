<?php

namespace Modules\KpiTool\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\KpiTool\Models\KpiDefinition;
use Modules\KpiTool\Models\KpiMonthlyTarget;

class KpiTargetController extends Controller
{
    public function index(): View
    {
        $definitions = KpiDefinition::query()->where('is_active', true)->where('is_template', false)->get();

        return view('kpitool::targets.index', compact('definitions'));
    }

    public function show(KpiDefinition $kpiDefinition): View
    {
        $targets = $kpiDefinition->monthlyTargets()->orderBy('year')->orderBy('month')->get();

        return view('kpitool::targets.show', compact('kpiDefinition', 'targets'));
    }

    public function store(Request $request, KpiDefinition $kpiDefinition): RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'target_value' => 'required|numeric',
            'growth_rate' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $kpiDefinition->monthlyTargets()->updateOrCreate(
            ['year' => $validated['year'], 'month' => $validated['month']],
            $validated + ['team_id' => auth()->user()->current_team_id]
        );

        return redirect()->route('kpitool.targets.show', $kpiDefinition)->with('success', __('Target saved.'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kpi_definition_id' => 'required|exists:kpitool_kpi_definitions,id',
            'start_year' => 'required|integer|min:2000|max:2100',
            'start_month' => 'required|integer|min:1|max:12',
            'months' => 'required|integer|min:1|max:60',
            'start_value' => 'required|numeric',
            'growth_rate' => 'required|numeric',
        ]);

        $definition = KpiDefinition::query()->findOrFail($validated['kpi_definition_id']);
        $value = (float) $validated['start_value'];
        $year = (int) $validated['start_year'];
        $month = (int) $validated['start_month'];

        for ($i = 0; $i < $validated['months']; $i++) {
            $definition->monthlyTargets()->updateOrCreate(
                ['year' => $year, 'month' => $month],
                [
                    'team_id' => auth()->user()->current_team_id,
                    'target_value' => round($value, 4),
                    'growth_rate' => $validated['growth_rate'],
                ]
            );

            $value *= (1 + ((float) $validated['growth_rate'] / 100));
            $month++;

            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }

        return redirect()->route('kpitool.targets.show', $definition)->with('success', __('Targets generated.'));
    }

    public function destroy(KpiMonthlyTarget $kpiMonthlyTarget): RedirectResponse
    {
        $definition = $kpiMonthlyTarget->kpiDefinition;
        $kpiMonthlyTarget->delete();

        return redirect()->route('kpitool.targets.show', $definition)->with('success', __('Target deleted.'));
    }
}
