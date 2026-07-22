<?php

namespace Modules\SmartKpi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\SmartKpi\Models\Company;
use Modules\SmartKpi\Models\Goal;
use Modules\SmartKpi\Models\KpiDefinition;

class GoalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Goal::with('company', 'department', 'kpiDefinition');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $goals = $query->latest()->paginate(15)->withQueryString();

        return view('smartkpi::goals.index', compact('goals'));
    }

    public function create(): View
    {
        return view('smartkpi::goals.form', [
            'goal' => new Goal,
            'companies' => Company::active()->get(),
            'kpis' => KpiDefinition::where('is_template', false)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $goal = Goal::create($validated + ['progress' => 0]);
        $goal->updateProgress();

        return redirect()->route('smartkpi.goals.index')->with('success', __('Goal created.'));
    }

    public function edit(Goal $goal): View
    {
        return view('smartkpi::goals.form', [
            'goal' => $goal,
            'companies' => Company::active()->get(),
            'kpis' => KpiDefinition::where('is_template', false)->get(),
        ]);
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $goal->update($validated);
        $goal->updateProgress();

        return redirect()->route('smartkpi.goals.index')->with('success', __('Goal updated.'));
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        $goal->delete();

        return redirect()->route('smartkpi.goals.index')->with('success', __('Goal deleted.'));
    }

    private function rules(): array
    {
        return [
            'company_id' => 'required|exists:smartkpi_companies,id',
            'department_id' => 'nullable|exists:smartkpi_departments,id',
            'kpi_definition_id' => 'nullable|exists:smartkpi_kpi_definitions,id',
            'name_en' => 'required|string|max:255',
            'name_de' => 'nullable|string|max:255',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'deadline' => 'nullable|date',
            'status' => 'nullable|string|in:active,achieved,missed',
        ];
    }
}
